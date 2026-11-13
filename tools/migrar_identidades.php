<?php
// Uso (una sola vez, después de importar migracion_seguridad.sql):
//   C:\xampp\php\php.exe tools\migrar_identidades.php
// Es idempotente: se puede repetir (por ejemplo tras importar datos_demo.sql).
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Solo línea de comandos.'); }
require_once __DIR__ . '/../includes/funciones.php';
$salida = [];

// Las cuentas importadas con la contraseña conocida 123456 deben cambiarla
// en el primer ingreso, tanto si ya existían como si acaban de migrarse.
function forzar_cambio_claves_predeterminadas() {
    global $pdo;
    $predeterminadas = todos('SELECT id_identidad,usuario FROM identidades WHERE password_hash=? AND debe_cambiar_password=0', [SEG_DUMMY_HASH]);
    if (!$predeterminadas) return;
    $pdo->beginTransaction();
    try {
        consulta('UPDATE identidades SET debe_cambiar_password=1 WHERE password_hash=? AND debe_cambiar_password=0', [SEG_DUMMY_HASH]);
        auditar('clave_predeterminada_cambio_obligatorio', 'ok', 'identidades', ['usuarios'=>array_column($predeterminadas, 'usuario')], 'alerta', null, 'migracion', 'sistema', true);
        $pdo->commit();
        echo "Se exigirá cambiar la contraseña inicial a ".count($predeterminadas)." cuenta(s).\n";
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fwrite(STDERR, "No se pudo forzar el cambio de las contraseñas predeterminadas.\n");
        exit(1);
    }
}
function insertar_legado($usuario, $nombre, $rol, $ref, $hash) {
    consulta('INSERT INTO identidades (usuario,nombre_completo,rol,ref_id,password_hash,estado,debe_cambiar_password,creada_en) VALUES (?,?,?,?,?,?,0,?)',
        [$usuario, $nombre, $rol, (string)$ref, $hash, 'activa', ahora()]);
    auditar('identidad_migrada', 'ok', 'usuario:' . $usuario, ['rol'=>$rol], 'info', null, 'migracion', 'sistema', true);
}
function hash_o_rehash($h) { return !empty(password_get_info($h)['algo']) ? $h : hash_password($h); }

foreach (todos('SELECT * FROM administradores') as $a) {
    if ($a['password'] === '!migrado' || uno('SELECT 1 FROM identidades WHERE usuario=?', [$a['usuario']])) continue;
    $pdo->beginTransaction();
    try {
        insertar_legado($a['usuario'], nombre($a), 'admin', $a['id_admin'], hash_o_rehash($a['password']));
        consulta("UPDATE administradores SET password='!migrado' WHERE id_admin=?", [$a['id_admin']]);
        $pdo->commit();
        echo "Administrador migrado: {$a['usuario']}\n";
    } catch (Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); fwrite(STDERR, "Error al migrar administrador {$a['usuario']}.\n"); exit(1); }
}
foreach (todos('SELECT * FROM docentes') as $d) {
    if ($d['password'] === '!migrado' || uno('SELECT 1 FROM identidades WHERE usuario=?', [$d['usuario']])) continue;
    $pdo->beginTransaction();
    try {
        insertar_legado($d['usuario'], nombre($d), 'docente', $d['id_docente'], hash_o_rehash($d['password']));
        consulta("UPDATE docentes SET password='!migrado' WHERE id_docente=?", [$d['id_docente']]);
        $pdo->commit();
        echo "Docente migrado: {$d['usuario']}\n";
    } catch (Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); fwrite(STDERR, "Error al migrar docente {$d['usuario']}.\n"); exit(1); }
}
forzar_cambio_claves_predeterminadas();
echo "\n--- CONTRASEÑAS TEMPORALES DE ESTUDIANTES (se muestran UNA sola vez; deberán cambiarlas al ingresar) ---\n";
$n = 0;
foreach (todos('SELECT * FROM estudiantes') as $e) {
    if (uno('SELECT 1 FROM identidades WHERE usuario=?', [$e['re']])) continue;
    $t = clave_temporal();
    $pdo->beginTransaction();
    try {
        consulta('INSERT INTO identidades (usuario,nombre_completo,rol,ref_id,password_hash,estado,debe_cambiar_password,creada_en) VALUES (?,?,?,?,?,?,1,?)',
            [$e['re'], nombre($e), 'estudiante', $e['re'], hash_password($t), 'activa', ahora()]);
        auditar('identidad_migrada', 'ok', 'usuario:' . $e['re'], ['rol'=>'estudiante'], 'info', null, 'migracion', 'sistema', true);
        $pdo->commit();
        echo str_pad($e['re'], 22) . $t . "\n"; $n++;
    } catch (Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); fwrite(STDERR, "Error al migrar estudiante {$e['re']}.\n"); exit(1); }
}
if (!$n) echo "(ningún estudiante nuevo)\n";
if (!uno('SELECT 1 FROM identidades WHERE es_emergencia=1')) {
    $p = clave_temporal() . clave_temporal();
    $pdo->beginTransaction();
    try {
        consulta("INSERT INTO administradores (ci,nombres,apellidos,usuario,password) VALUES ('EMERGENCIA','Cuenta','de Emergencia','emergencia','!migrado')");
        crear_identidad('emergencia', 'Cuenta de Emergencia', 'admin', $pdo->lastInsertId(), $p, 0, 1, 'suspendida');
        $pdo->commit();
        echo "\n--- CUENTA DE EMERGENCIA (guárdala impresa en sobre sellado; está SUSPENDIDA hasta que se habilite) ---\nusuario: emergencia\nclave:   $p\n";
    } catch (Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); fwrite(STDERR, "No se pudo crear la cuenta de emergencia.\n"); exit(1); }
}
$k = 0;
foreach (todos('SELECT * FROM kardex WHERE sello IS NULL') as $f) { sellar_nota($f['id_kardex']); registrar_historial($f['id_kardex'], 'migracion', null, $f); $k++; }
echo "\nCalificaciones selladas para control de integridad: $k\nListo.\n";
