<?php
// Núcleo de seguridad: configuración, contraseñas, CSRF, auditoría, login, sesiones, autorización y 2FA.
require_once __DIR__ . '/totp.php';
const SEG_DUMMY_HASH = '$2y$12$yQ3IvaYklUG337Ke19pZ/u9cAW8g/cAmBrTREeg63Etf5qDv3sNG2';
const SEG_ESCRITURA_ACADEMICA = ['registrar_nota.php','editar_nota.php','eliminar.php','nuevo_estudiante.php','editar_estudiante.php','nuevo_profesor.php','editar_profesor.php','nueva_materia.php','editar_materia.php'];

function cfg($clave, $defecto = null) {
    global $pdo;
    if (!isset($GLOBALS['_cfg'])) {
        $GLOBALS['_cfg'] = [];
        try { foreach ($pdo->query('SELECT clave,valor FROM configuracion') as $r) $GLOBALS['_cfg'][$r['clave']] = $r['valor']; } catch (PDOException $e) {}
    }
    return $GLOBALS['_cfg'][$clave] ?? $defecto;
}
function ip_cliente() { return substr($_SERVER['REMOTE_ADDR'] ?? 'cli', 0, 45); }
function ahora() { return date('Y-m-d H:i:s'); }

// ---------- Cifrado de secretos (2FA) con AES-256-GCM ----------
function clave_app() {
    static $k = null; if ($k) return $k;
    $env = getenv('KARDEX_APP_KEY');
    if ($env) return $k = hash('sha256', $env, true);
    $f = __DIR__ . '/../storage/app.key';
    if (!is_file($f)) { if (!is_dir(dirname($f))) mkdir(dirname($f), 0700, true); file_put_contents($f, bin2hex(random_bytes(32))); @chmod($f, 0600); }
    return $k = hash('sha256', trim(file_get_contents($f)), true);
}
function cifrar($t) { $iv = random_bytes(12); $c = openssl_encrypt($t, 'aes-256-gcm', clave_app(), OPENSSL_RAW_DATA, $iv, $tag); return base64_encode($iv . $tag . $c); }
function descifrar($b) {
    $r = base64_decode((string)$b, true); if ($r === false || strlen($r) < 29) return null;
    $p = openssl_decrypt(substr($r, 28), 'aes-256-gcm', clave_app(), OPENSSL_RAW_DATA, substr($r, 0, 12), substr($r, 12, 16));
    return $p === false ? null : $p;
}

// ---------- Contraseñas (Argon2id; bcrypt costo 12 si el PHP no lo trae) ----------
function opts_hash() {
    return defined('PASSWORD_ARGON2ID') ? [PASSWORD_ARGON2ID, ['memory_cost'=>65536,'time_cost'=>3,'threads'=>1]] : [PASSWORD_BCRYPT, ['cost'=>12]];
}
function hash_password($p) { [$a, $o] = opts_hash(); return password_hash($p, $a, $o); }
function necesita_rehash($h) { [$a, $o] = opts_hash(); return password_needs_rehash($h, $a, $o); }
function validar_password($p, $usuario = '') {
    if (strlen($p) < 10) return 'La contraseña debe tener al menos 10 caracteres.';
    if (strlen($p) > 128) return 'La contraseña es demasiado larga (máximo 128).';
    if (!preg_match('/[a-z]/', $p) || !preg_match('/[A-Z]/', $p) || !preg_match('/\d/', $p)) return 'Debe combinar mayúsculas, minúsculas y números.';
    if ($usuario !== '' && stripos($p, $usuario) !== false) return 'La contraseña no debe contener el nombre de usuario.';
    return '';
}
function clave_temporal() {
    $g = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789']; $todo = implode('', $g); $c = [];
    foreach ($g as $s) $c[] = $s[random_int(0, strlen($s) - 1)];
    while (count($c) < 12) $c[] = $todo[random_int(0, strlen($todo) - 1)];
    for ($i = count($c) - 1; $i > 0; $i--) { $j = random_int(0, $i); [$c[$i], $c[$j]] = [$c[$j], $c[$i]]; }
    return implode('', $c);
}

// ---------- CSRF ----------
function csrf_token() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_campo() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_validar() {
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        auditar('csrf_invalido', 'denegado', basename($_SERVER['SCRIPT_NAME'] ?? ''), 'Token ausente o inválido', 'alerta');
        http_response_code(403); exit('Solicitud rechazada: token de seguridad inválido. Vuelve atrás y recarga la página.');
    }
}

// ---------- Auditoría con cadena de hashes (Parte 4) ----------
function auditar($evento, $resultado = 'ok', $recurso = '', $detalle = '', $sev = 'info', $idIdent = null, $usuario = null, $rol = null, $obligatoria = false) {
    global $pdo; $own = false;
    try {
        $idIdent = $idIdent ?? ($_SESSION['id_identidad'] ?? null); $usuario = $usuario ?? ($_SESSION['usuario'] ?? ''); $rol = $rol ?? ($_SESSION['rol'] ?? '');
        $detalle = is_array($detalle) ? json_encode($detalle, JSON_UNESCAPED_UNICODE) : (string)$detalle;
        $own = !$pdo->inTransaction(); if ($own) $pdo->beginTransaction();
        $prev = $pdo->query('SELECT hash FROM auditoria ORDER BY id DESC LIMIT 1 FOR UPDATE')->fetchColumn() ?: str_repeat('0', 64);
        $m = ahora(); $ip = ip_cliente();
        $h = hash('sha256', implode('|', [$prev, $m, $idIdent, $usuario, $rol, $evento, $resultado, $recurso, $detalle, $ip, $sev]));
        $pdo->prepare('INSERT INTO auditoria (momento,id_identidad,usuario,rol,evento,resultado,recurso,detalle,ip,severidad,hash_prev,hash) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$m, $idIdent, $usuario, $rol, $evento, $resultado, $recurso, $detalle, $ip, $sev, $prev, $h]);
        if ($own) $pdo->commit();
    } catch (Throwable $e) {
        if ($own && $pdo->inTransaction()) $pdo->rollBack();
        error_log('auditoria: ' . $e->getMessage());
        // En eventos críticos el llamador debe cancelar la operación protegida.
        if ($obligatoria) throw new RuntimeException('No se pudo registrar el evento de auditoría; la operación fue cancelada.', 0, $e);
    }
}

// ---------- Identidades (Parte 1) ----------
function crear_identidad($usuario, $nombre, $rol, $refId, $passwordPlano, $debeCambiar = 1, $esEmergencia = 0, $estado = 'activa') {
    global $pdo;
    $own = !$pdo->inTransaction();
    if ($own) $pdo->beginTransaction();
    try {
        consulta('INSERT INTO identidades (usuario,nombre_completo,rol,ref_id,password_hash,estado,debe_cambiar_password,es_emergencia,creada_en) VALUES (?,?,?,?,?,?,?,?,?)',
            [$usuario, $nombre, $rol, (string)$refId, hash_password($passwordPlano), $estado, $debeCambiar, $esEmergencia, ahora()]);
        $id = (int)$pdo->lastInsertId();
        auditar('identidad_creada', 'ok', 'identidad#' . $id, ['usuario'=>$usuario, 'rol'=>$rol, 'estado'=>$estado], 'info', null, null, null, true);
        if ($own) $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        if ($own && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function revocar_sesiones($idIdent, $motivo, $excepto = null) {
    consulta('UPDATE sesiones SET revocada=1, motivo_revocacion=? WHERE id_identidad=? AND revocada=0 AND id_sesion<>?', [$motivo, $idIdent, $excepto ?? '']);
}
// Cambio de estado: una identidad suspendida o revocada deja de operar de inmediato (sus sesiones se revocan).
function cambiar_estado_identidad($i, $nuevo, $motivo) {
    global $pdo;
    $own = !$pdo->inTransaction();
    if ($own) $pdo->beginTransaction();
    try {
        consulta('UPDATE identidades SET estado=?, estado_cambiado_en=? WHERE id_identidad=?', [$nuevo, ahora(), $i['id_identidad']]);
        revocar_sesiones($i['id_identidad'], 'estado_' . $nuevo);
        $liberadas = [];
        if ($nuevo === 'revocada' && in_array($i['rol'], ['docente', 'coordinador'], true)) {
            // Las notas se conservan; solo se liberan las materias para reasignarlas.
            $liberadas = array_column(todos('SELECT id_materia FROM materias WHERE id_docente=?', [$i['ref_id']]), 'id_materia');
            consulta('UPDATE materias SET id_docente=NULL WHERE id_docente=?', [$i['ref_id']]);
        }
        auditar('identidad_estado', 'ok', 'identidad#' . $i['id_identidad'], ['usuario'=>$i['usuario'], 'de'=>$i['estado'], 'a'=>$nuevo, 'motivo'=>$motivo, 'materias_liberadas'=>$liberadas], $nuevo === 'revocada' ? 'alerta' : 'info', null, null, null, true);
        if ($own) $pdo->commit();
    } catch (Throwable $e) {
        if ($own && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function otros_admin_activos($idIdent) {
    return (int)valor("SELECT COUNT(*) FROM identidades WHERE rol='admin' AND estado='activa' AND es_emergencia=0 AND id_identidad<>?", [$idIdent]);
}
function badge_estado($e) {
    if ($e === 'suspendida') return '<span class="badge warn">Suspendida</span>';
    if ($e === 'revocada') return '<span class="badge bad">De baja</span>';
    return '';
}
function docentes_activos($incluir = null) {
    return todos("SELECT d.* FROM docentes d LEFT JOIN identidades i ON i.rol='docente' AND i.ref_id=d.id_docente WHERE i.estado='activa' OR d.id_docente=? ORDER BY d.apellidos,d.nombres", [$incluir ?? 0]);
}

// ---------- Autenticación (Parte 2) ----------
function intentar_login($usuario, $password) {
    global $pdo;
    $usuario = mb_substr(trim($usuario), 0, 50); $clave = mb_strtolower($usuario);
    if (strlen($password) > 128) $password = '';
    $max = (int)cfg('max_intentos', 5); $win = (int)cfg('bloqueo_minutos', 15);
    $ip = ip_cliente(); $ahora = ahora(); $desde = date('Y-m-d H:i:s', time() - $win * 60);
    consulta('DELETE FROM intentos_login WHERE momento < ?', [date('Y-m-d H:i:s', time() - 86400)]);
    consulta("DELETE FROM sesiones WHERE expira_en < ?", [date('Y-m-d H:i:s', time() - 7 * 86400)]);
    $fu = (int)valor('SELECT COUNT(*) FROM intentos_login WHERE exito=0 AND usuario=? AND momento>?', [$clave, $desde]);
    $fi = (int)valor('SELECT COUNT(*) FROM intentos_login WHERE exito=0 AND ip=? AND momento>?', [$ip, $desde]);
    if ($fu >= $max || $fi >= $max * 4) {
        // Una sola entrada de auditoría por bloqueo (evita inundar el registro si el atacante insiste).
        if ((int)valor("SELECT COUNT(*) FROM auditoria WHERE evento='login_bloqueado' AND usuario=? AND momento>?", [$clave, $desde]) === 0)
            auditar('login_bloqueado', 'denegado', '', 'Demasiados intentos fallidos', 'alerta', null, $clave, '');
        // 429 + Retry-After: permite que el servidor (fail2ban) y los clientes reconozcan el bloqueo.
        http_response_code(429); header('Retry-After: ' . ($win * 60));
        return [false, 'Demasiados intentos fallidos. Espera unos minutos antes de volver a intentar.'];
    }
    $u = $usuario !== '' ? uno('SELECT * FROM identidades WHERE usuario=?', [$usuario]) : false;
    $okPass = password_verify($password, $u ? $u['password_hash'] : SEG_DUMMY_HASH) && $u;
    $motivo = null;
    if (!$okPass) $motivo = 'credenciales';
    elseif ($u['estado'] !== 'activa') $motivo = 'estado_' . $u['estado'];
    elseif ($u['vigente_hasta'] && strtotime($u['vigente_hasta']) < time()) $motivo = 'vigencia_vencida';
    elseif (cfg('modo_sistema', 'normal') === 'bloqueado' && !$u['es_emergencia']) $motivo = 'sistema_bloqueado';
    if ($motivo) {
        consulta('INSERT INTO intentos_login (usuario,ip,momento,exito) VALUES (?,?,?,0)', [$clave, $ip, $ahora]);
        auditar('login_fallido', 'fallo', '', 'motivo=' . $motivo, $motivo === 'credenciales' ? 'info' : 'alerta', $u ? $u['id_identidad'] : null, $clave, $u ? $u['rol'] : '');
        // Mismo mensaje para cuenta inexistente, clave errónea, cuenta suspendida o revocada.
        return [false, 'Credenciales incorrectas o cuenta no disponible.'];
    }
    session_regenerate_id(true);
    $_SESSION = ['id_identidad'=>(int)$u['id_identidad'], 'usuario_id'=>$u['ref_id'], 'usuario'=>$u['usuario'], 'rol'=>$u['rol'], 'nombre_completo'=>$u['nombre_completo'], 'es_emergencia'=>(int)$u['es_emergencia']];
    try {
        $pdo->beginTransaction();
        consulta('DELETE FROM intentos_login WHERE usuario=? AND exito=0', [$clave]);
        consulta('INSERT INTO sesiones (id_sesion,id_identidad,creada_en,ultima_actividad,expira_en,ip,user_agent) VALUES (?,?,?,?,?,?,?)',
            [hash('sha256', session_id()), $u['id_identidad'], $ahora, $ahora, date('Y-m-d H:i:s', time() + (int)cfg('sesion_maxima_horas', 8) * 3600), $ip, mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
        if (necesita_rehash($u['password_hash'])) consulta('UPDATE identidades SET password_hash=? WHERE id_identidad=?', [hash_password($password), $u['id_identidad']]);
        consulta('UPDATE identidades SET ultimo_login=? WHERE id_identidad=?', [$ahora, $u['id_identidad']]);
        auditar('login_ok', 'ok', '', '', 'info', null, null, null, true);
        if ($u['es_emergencia']) auditar('emergencia_uso', 'ok', '', 'Ingreso con cuenta de emergencia', 'critica', null, null, null, true);
        $pdo->commit();
        return [true, ''];
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION = [];
        error_log('login_ok: '.$ex->getMessage());
        return [false, 'No se pudo completar el inicio de sesión de forma segura. Inténtalo nuevamente.'];
    }
}
function rotar_sesion() {
    $viejo = hash('sha256', session_id()); session_regenerate_id(true);
    consulta('UPDATE sesiones SET id_sesion=? WHERE id_sesion=?', [hash('sha256', session_id()), $viejo]);
}
function cerrar_sesion($motivo = 'logout') {
    if (!empty($_SESSION['id_identidad'])) consulta('UPDATE sesiones SET revocada=1, motivo_revocacion=? WHERE id_sesion=?', [$motivo, hash('sha256', session_id())]);
    $_SESSION = [];
    if (PHP_SAPI !== 'cli' && ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', ['expires'=>time()-42000, 'path'=>$p['path'], 'secure'=>$p['secure'], 'httponly'=>true, 'samesite'=>'Strict']); }
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
}
// Valida en CADA petición: sesión vigente en el servidor, identidad activa, rol actual, modo del sistema y CSRF.
function verificarSesion() {
    if (empty($_SESSION['id_identidad'])) ir('login.php');
    $s = uno('SELECT s.*, i.estado, i.vigente_hasta, i.rol AS rol_actual, i.ref_id, i.usuario, i.nombre_completo, i.debe_cambiar_password, i.es_emergencia
              FROM sesiones s JOIN identidades i ON i.id_identidad=s.id_identidad WHERE s.id_sesion=?', [hash('sha256', session_id())]);
    $t = time(); $m = null;
    if (!$s || (int)$s['id_identidad'] !== (int)$_SESSION['id_identidad']) $m = 'sin_registro';
    elseif ($s['revocada']) $m = 'revocada';
    elseif (strtotime($s['expira_en']) < $t) $m = 'expirada';
    elseif (strtotime($s['ultima_actividad']) + (int)cfg('inactividad_minutos', 15) * 60 < $t) $m = 'inactividad';
    elseif ($s['estado'] !== 'activa') $m = 'identidad_' . $s['estado'];
    elseif ($s['vigente_hasta'] && strtotime($s['vigente_hasta']) < $t) $m = 'vigencia_vencida';
    elseif (cfg('modo_sistema', 'normal') === 'bloqueado' && !$s['es_emergencia']) $m = 'sistema_bloqueado';
    if ($m) {
        auditar('sesion_cerrada', 'denegado', '', $m, 'info');
        cerrar_sesion($m);
        ir('login.php?m=' . (in_array($m, ['inactividad', 'expirada', 'revocada'], true) ? $m : 'cuenta'));
    }
    $_SESSION['rol'] = $s['rol_actual']; $_SESSION['usuario_id'] = $s['ref_id']; $_SESSION['usuario'] = $s['usuario'];
    $_SESSION['nombre_completo'] = $s['nombre_completo']; $_SESSION['es_emergencia'] = (int)$s['es_emergencia'];
    consulta('UPDATE sesiones SET ultima_actividad=? WHERE id_sesion=?', [ahora(), $s['id_sesion']]);
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($s['debe_cambiar_password'] && !in_array($script, ['cambiar_password.php', 'logout.php'], true)) ir('cambiar_password.php');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_validar();
        if (cfg('modo_sistema', 'normal') !== 'normal' && in_array($script, SEG_ESCRITURA_ACADEMICA, true)) {
            auditar('operacion_bloqueada', 'denegado', $script, 'Modo ' . cfg('modo_sistema'), 'alerta');
            http_response_code(503); exit('El sistema está en modo de contingencia: las modificaciones académicas están bloqueadas temporalmente. Contacta al administrador.');
        }
    }
}

// ---------- Autorización (Parte 3) ----------
function permiso_rol($rol, $op) {
    global $pdo;
    if (!isset($GLOBALS['_perm'])) {
        $GLOBALS['_perm'] = [];
        try { foreach ($pdo->query('SELECT rol,operacion FROM permisos') as $r) $GLOBALS['_perm'][$r['rol'] . '|' . $r['operacion']] = true; } catch (PDOException $e) {}
    }
    return isset($GLOBALS['_perm'][$rol . '|' . $op]);
}
function tiene_permiso($op) { return permiso_rol($_SESSION['rol'] ?? '', $op); }
// Evalúa identidad (sesión), operación, recurso, permisos vigentes y contexto (relación persona/objeto).
function autorizar($op, $recurso = [], $silencioso = false) {
    $rol = $_SESSION['rol'] ?? ''; $ok = permiso_rol($rol, $op); $razon = 'sin_permiso';
    if ($ok && $rol === 'docente' && in_array($op, ['nota.crear', 'nota.editar', 'nota.eliminar'], true) && array_key_exists('id_materia', $recurso)) {
        $ok = materiaPropia($recurso['id_materia']); $razon = 'materia_ajena';
        if ($ok && array_key_exists('re_estudiante', $recurso)) {
            $ok = materiaCursoPropia($recurso['id_materia'], $recurso['re_estudiante']);
            $razon = 'estudiante_fuera_del_curso_asignado';
        }
    }
    // El docente solo consulta estudiantes de cursos asignados a sus materias.
    if ($ok && $rol === 'docente' && $op === 'kardex.ver' && array_key_exists('re', $recurso)) {
        $ok = (bool)valor('SELECT COUNT(*) FROM materia_cursos mc INNER JOIN materias m ON m.id_materia=mc.id_materia INNER JOIN estudiantes e ON e.curso=mc.curso WHERE e.re=? AND m.id_docente=?', [$recurso['re'], $_SESSION['usuario_id'] ?? 0]);
        $razon = 'estudiante_sin_relacion_docente';
    }
    if ($ok && $rol === 'estudiante' && $op === 'kardex.ver' && array_key_exists('re', $recurso)) {
        $ok = (string)$recurso['re'] === (string)($_SESSION['usuario_id'] ?? ''); $razon = 'kardex_ajeno';
    }
    if ($ok && strpos($op, 'identidad.') === 0 && isset($recurso['id_identidad']) && (int)$recurso['id_identidad'] === (int)($_SESSION['id_identidad'] ?? 0)) {
        $ok = false; $razon = 'auto_modificacion';
    }
    if (!$ok) {
        auditar('acceso_denegado', 'denegado', $op, ['razon'=>$razon, 'recurso'=>$recurso, 'pagina'=>basename($_SERVER['SCRIPT_NAME'] ?? '')], 'alerta');
        if (!$silencioso) sinPermiso();
        return false;
    }
    return true;
}

// ---------- Segundo factor para operaciones de alto riesgo (Parte 2e) ----------
function mfa_campo() {
    if (!in_array(rol(), ['admin','docente'], true) || !empty($_SESSION['es_emergencia'])) return '';
    return '<div class="field"><label for="codigo_mfa">Código de verificación (2º factor)</label><input id="codigo_mfa" name="codigo_mfa" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required placeholder="6 dígitos de tu app autenticadora"></div>';
}
function exigir_mfa(&$error) {
    if (!in_array(rol(), ['admin','docente'], true)) return true;
    if (!empty($_SESSION['es_emergencia'])) { auditar('emergencia_operacion', 'ok', basename($_SERVER['SCRIPT_NAME'] ?? ''), 'Operación sensible con cuenta de emergencia', 'critica'); return true; }
    $id = (int)$_SESSION['id_identidad']; $clave = 'mfa#' . $id;
    $desde = date('Y-m-d H:i:s', time() - (int)cfg('bloqueo_minutos', 15) * 60);
    if ((int)valor('SELECT COUNT(*) FROM intentos_login WHERE exito=0 AND usuario=? AND momento>?', [$clave, $desde]) >= (int)cfg('max_intentos', 5)) { $error = 'Demasiados códigos incorrectos. Espera unos minutos.'; return false; }
    $r = uno('SELECT mfa_secreto,mfa_activo,mfa_ultimo_paso FROM identidades WHERE id_identidad=?', [$id]);
    if (!$r || !$r['mfa_activo'] || !$r['mfa_secreto']) { $error = 'Para esta operación debes activar primero tu segundo factor en «Mi seguridad (2FA)».'; return false; }
    $paso = totp_verificar(descifrar($r['mfa_secreto']), preg_replace('/\D/', '', post('codigo_mfa')), (int)$r['mfa_ultimo_paso']);
    if ($paso === false) {
        consulta('INSERT INTO intentos_login (usuario,ip,momento,exito) VALUES (?,?,?,0)', [$clave, ip_cliente(), ahora()]);
        auditar('mfa_fallido', 'fallo', basename($_SERVER['SCRIPT_NAME'] ?? ''), '', 'alerta'); $error = 'Código de verificación incorrecto.'; return false;
    }
    global $pdo;
    $pdo->beginTransaction();
    try {
        consulta('UPDATE identidades SET mfa_ultimo_paso=? WHERE id_identidad=?', [$paso, $id]);
        consulta('DELETE FROM intentos_login WHERE usuario=? AND exito=0', [$clave]);
        auditar('mfa_verificado', 'ok', basename($_SERVER['SCRIPT_NAME'] ?? ''), '', 'info', null, null, null, true);
        $pdo->commit();
        return true;
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('verificar_mfa: '.$ex->getMessage());
        $error = 'No se pudo completar la verificación de seguridad. Inténtalo nuevamente.';
        return false;
    }
}