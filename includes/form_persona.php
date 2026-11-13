<?php
require_once __DIR__.'/layout.php';
verificarSesion();
$esEstudiante = $tipo === 'estudiante';
autorizar(($esEstudiante ? 'estudiante.' : 'docente.').($editar ? 'editar' : 'crear'));
$tabla = $esEstudiante ? 'estudiantes' : 'docentes';
$clave = $esEstudiante ? 're' : 'id_docente';
$lista = $esEstudiante ? 'estudiantes.php' : 'profesores.php';
$id = $_GET[$esEstudiante ? 're' : 'id'] ?? '';
$datos = $editar ? uno("SELECT * FROM $tabla WHERE $clave = ?", [$id]) : [];
if ($editar && !$datos) noExiste($esEstudiante ? 'Estudiante' : 'Docente');
$datos = array_merge(['re'=>'','ci'=>'','nombres'=>'','apellidos'=>'','curso'=>'','fecha_nacimiento'=>'','usuario'=>''], $datos ?: []);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = $esEstudiante ? ['re','nombres','apellidos','curso','fecha_nacimiento'] : ['ci','nombres','apellidos','usuario'];
    $usuarioOriginal = $datos['usuario'];
    foreach($campos as $c) $datos[$c] = post($c);
    // El identificador de una identidad es inmutable: no se cambia al editar.
    if ($editar) { if ($esEstudiante) $datos['re'] = $id; else $datos['usuario'] = $usuarioOriginal; }
    $password = (string)($_POST['password'] ?? '');
    foreach($campos as $c) if ($datos[$c] === '') $error = 'Completa todos los campos obligatorios.';
    foreach(['nombres'=>100,'apellidos'=>100,'re'=>20,'ci'=>20,'curso'=>50,'usuario'=>50] as $c=>$max) if (longitud($datos[$c]) > $max) $error = 'Uno de los campos supera la longitud permitida.';
    $idLogin = $esEstudiante ? $datos['re'] : $datos['usuario'];
    if (!$error && !$editar && !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $idLogin)) $error = 'El '.($esEstudiante ? 'registro estudiantil' : 'usuario').' admite letras, números, punto, guion y guion bajo (3 a 50 caracteres).';
    if (!$error && !$esEstudiante && !$editar) $error = validar_password($password, $datos['usuario']);
    if ($esEstudiante) {
        $fecha = DateTime::createFromFormat('!Y-m-d', $datos['fecha_nacimiento']);
        if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha_nacimiento'] || $datos['fecha_nacimiento'] > date('Y-m-d')) $error = 'Ingresa una fecha de nacimiento válida, anterior o igual a hoy.';
    }
    if (!$error && !$editar && valor('SELECT COUNT(*) FROM identidades WHERE usuario=?', [$idLogin])) $error = 'Ese identificador ya está en uso por otra cuenta. Elige otro.';
    if (!$error && !exigir_mfa($error)) { /* MFA requerido para cambios de datos personales e identidades. */ }
    if (!$error) try {
        $pdo->beginTransaction(); $aviso = '';
        if ($esEstudiante) {
            if ($editar) {
                consulta('UPDATE estudiantes SET nombres=?, apellidos=?, curso=?, fecha_nacimiento=? WHERE re=?',[$datos['nombres'],$datos['apellidos'],$datos['curso'],$datos['fecha_nacimiento'],$id]);
                consulta("UPDATE identidades SET nombre_completo=? WHERE rol='estudiante' AND ref_id=?", [nombre($datos), $id]);
            } else {
                consulta('INSERT INTO estudiantes (re,nombres,apellidos,curso,fecha_nacimiento) VALUES (?,?,?,?,?)',array_map(fn($c)=>$datos[$c],$campos));
                $temp = clave_temporal(); crear_identidad($datos['re'], nombre($datos), 'estudiante', $datos['re'], $temp, 1);
                $aviso = ' Contraseña temporal (se muestra una sola vez): '.$temp;
            }
        } else {
            if ($editar) {
                consulta('UPDATE docentes SET ci=?,nombres=?,apellidos=? WHERE id_docente=?',[$datos['ci'],$datos['nombres'],$datos['apellidos'],$id]);
                consulta("UPDATE identidades SET nombre_completo=? WHERE rol IN ('docente','coordinador') AND ref_id=?", [nombre($datos), $id]);
            } else {
                consulta("INSERT INTO docentes (ci,nombres,apellidos,usuario,password) VALUES (?,?,?,?,'!migrado')",[$datos['ci'],$datos['nombres'],$datos['apellidos'],$datos['usuario']]);
                crear_identidad($datos['usuario'], nombre($datos), 'docente', $pdo->lastInsertId(), $password, 1);
            }
        }
        auditar(($esEstudiante ? 'estudiante_' : 'docente_').($editar ? 'editado' : 'creado'), 'ok', ($esEstudiante ? 'estudiante#'.$datos['re'] : 'docente#'.($editar ? $id : $datos['usuario'])), ['nombre'=>nombre($datos), 'curso'=>$datos['curso'] ?: null], 'info', null, null, null, true);
        $pdo->commit();
        aviso(($esEstudiante ? 'Estudiante' : 'Docente').($editar ? ' actualizado correctamente.' : ' registrado correctamente.').$aviso); ir($lista);
    } catch(PDOException $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); $error = errorBD($ex); }
      catch(Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log('guardar_persona: '.$ex->getMessage()); $error = 'No se pudo guardar la ficha y su auditoría de forma segura.'; }
}
$titulo = ($editar ? 'Editar ' : 'Nuevo ').($esEstudiante ? 'estudiante' : 'docente');
cabecera($titulo,$esEstudiante ? 'estudiantes' : 'docentes','Completa la ficha para mantener la información académica al día.');
?>
<a class="back-link" href="<?= $lista ?>">← Volver a <?= $esEstudiante ? 'estudiantes' : 'docentes' ?>
</a>
<div class="form-layout">
<section class="panel">
<div class="panel-head">
<div>
<h2>Datos <?= $esEstudiante ? 'del estudiante' : 'del docente' ?>
</h2>
<p>
<?= $editar ? 'Revisa y actualiza los campos necesarios.' : 'Todos los campos son obligatorios.' ?>
</p>
</div>
</div>
<div class="panel-body">
<?php errorFormulario($error); ?>
<form method="post">
<?= csrf_campo() ?>
<div class="form-grid">
<?php
if ($esEstudiante) campo('re','Registro estudiantil',$datos['re'],'text','required maxlength="20" placeholder="Ej. RE-2026-01" '.($editar ? 'readonly' : ''));
else campo('ci','Cédula de identidad',$datos['ci'],'text','required maxlength="20" placeholder="Ej. 7654321"');
campo('nombres','Nombres',$datos['nombres'],'text','required maxlength="100"');
campo('apellidos','Apellidos',$datos['apellidos'],'text','required maxlength="100"');
if ($esEstudiante) {
    campo('curso','Curso y paralelo',$datos['curso'],'text','required maxlength="50" list="cursos" placeholder="Ej. 6to Secundaria A"');
    campo('fecha_nacimiento','Fecha de nacimiento',$datos['fecha_nacimiento'],'date','required max="'.date('Y-m-d').'"');
} else {
    campo('usuario','Usuario de acceso',$datos['usuario'],'text','required maxlength="50" autocomplete="off" '.($editar ? 'readonly' : ''));
    if (!$editar) campo('password','Contraseña inicial (temporal)','','password','required minlength="10" maxlength="128" autocomplete="new-password"');
}
?>
</div>
<?php if($esEstudiante): ?>
<datalist id="cursos">
<?php foreach(todos('SELECT DISTINCT curso FROM estudiantes ORDER BY curso') as $c): ?>
<option value="<?= e($c['curso']) ?>">
<?php endforeach ?>
</datalist>
<?php endif ?>
<?= mfa_campo() ?>
<div class="form-actions">
<a class="button secondary" href="<?= $lista ?>">Cancelar</a>
<button class="button" type="submit">
<?= $editar ? 'Guardar cambios' : 'Registrar '.($esEstudiante ? 'estudiante' : 'docente') ?>
</button>
</div>
</form>
</div>
</section>
<aside class="note-panel">
<h3>Una ficha completa</h3>
<p>
<?= $esEstudiante ? 'El registro estudiantil identifica al alumno y permite consultar su kardex. Utiliza un curso y paralelo consistentes para agrupar correctamente a los estudiantes.' : 'Después de registrar al docente, puedes asignarle una o varias materias desde el módulo Materias.' ?>
</p>
<p>
<?= $esEstudiante ? 'La fecha de nacimiento forma parte de los datos requeridos del estudiante.' : ($editar ? 'El usuario no se puede cambiar. Para restablecer la contraseña usa el módulo Identidades.' : 'El docente ingresará con el usuario y la contraseña inicial que registres aquí y deberá cambiarla en su primer ingreso.') ?>
</p>
</aside>
</div>
<?php pie(); ?>
