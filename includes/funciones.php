<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/integridad.php';
function e($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function consulta($sql, $params = []) { global $pdo; $s = $pdo->prepare($sql); $s->execute($params); return $s; }
function uno($sql, $params = []) { return consulta($sql, $params)->fetch(); }
function todos($sql, $params = []) { return consulta($sql, $params)->fetchAll(); }
function valor($sql, $params = []) { return consulta($sql, $params)->fetchColumn(); }
function ir($url) { header('Location: ' . $url); exit; }
function aviso($texto, $tipo = 'success') { $_SESSION['aviso'] = [$texto, $tipo]; }
function rol() { return $_SESSION['rol'] ?? ''; }
function inicio() { $m = ['admin'=>'index.php','docente'=>'mis_materias.php','coordinador'=>'notas.php','auditor'=>'auditoria.php','estudiante'=>'mi_kardex.php']; return $m[rol()] ?? 'login.php'; }
function admin() { verificarSesion(); if (rol() !== 'admin') { auditar('acceso_denegado','denegado',basename($_SERVER['SCRIPT_NAME'] ?? ''),'Sección solo para administración','alerta'); sinPermiso('Esta sección es solo para administración.'); } }
// ¿La materia está asignada al docente que inició sesión?
function materiaPropia($idMateria) { return (bool)valor('SELECT COUNT(*) FROM materias WHERE id_materia=? AND id_docente=?',[$idMateria,$_SESSION['usuario_id'] ?? 0]); }
// Relación contextual: el docente debe tener asignada la materia y el curso del estudiante.
function materiaCursoPropia($idMateria, $reEstudiante) {
    return (bool)valor('SELECT COUNT(*) FROM materia_cursos mc INNER JOIN materias m ON m.id_materia=mc.id_materia INNER JOIN estudiantes e ON e.curso=mc.curso WHERE mc.id_materia=? AND e.re=? AND m.id_docente=?', [$idMateria, $reEstudiante, $_SESSION['usuario_id'] ?? 0]);
}
// El admin gestiona todas las notas; el docente solo las de sus materias y cursos asignados.
function puedeGestionarNota($nota) { return autorizar('nota.editar',['id_materia'=>$nota['id_materia']],true); }
function sinPermiso($mensaje = 'Solo puedes gestionar las calificaciones de tus propias materias.') {
    http_response_code(403); require_once __DIR__.'/layout.php'; cabecera('Sin permiso');
    echo '<div class="panel empty">
<h2>No tienes permiso para esta acción</h2>
<p>'.e($mensaje).'</p>
<a class="button" href="'.e(inicio()).'">Volver al inicio</a>
</div>'; pie(); exit;
}
function post($key, $default = '') { return trim((string)($_POST[$key] ?? $default)); }
function inicial($texto) { preg_match('/^./us', (string)$texto, $m); return $m[0] ?? 'U'; }
function longitud($texto) { return preg_match_all('/./us', (string)$texto); }
function nombre($r) { return trim(($r['nombres'] ?? '') . ' ' . ($r['apellidos'] ?? '')); }
function seleccionado($a, $b) { return (string)$a === (string)$b ? ' selected' : ''; }
function errorBD($ex) {
    $c = $ex->errorInfo[1] ?? 0;
    if ($c === 1062) return 'Ya existe un registro con esos datos (CI, usuario, nombre de materia o una nota repetida del mismo trimestre).';
    if ($c === 1451 || $c === 1452) return 'No se pudo guardar: el registro está relacionado con otros datos.';
    if ($c === 4025) return 'Algún valor está fuera del rango permitido (nota de 0 a 100, trimestre de 1 a 3).';
    if ($ex->getCode() === '23000') return 'No se pudo guardar: revisa los datos duplicados o relacionados con otros registros.';
    return 'No se pudo completar la operación. Revisa los campos y la conexión a la base de datos.';
}
function campo($name, $label, $value = '', $type = 'text', $extra = '') {
    echo '<div class="field">
<label for="'.e($name).'">'.e($label).'</label>
<input id="'.e($name).'" name="'.e($name).'" type="'.e($type).'" value="'.e($value).'" '.$extra.'>
</div>';
}
function errorFormulario($error) { if ($error) echo '<div class="alert error" role="alert">'.e($error).'</div>'; }
function vacio($cols, $message) { echo '<tr>
<td colspan="'.$cols.'">
<div class="empty">
<strong>No hay registros para mostrar</strong>
<span>'.e($message).'</span>
</div>
</td>
</tr>'; }
function noExiste($nombre = 'Registro') {
    http_response_code(404); require_once __DIR__.'/layout.php'; cabecera('Registro no encontrado');
    echo '<div class="panel empty">
<h2>'.e($nombre).' no encontrado</h2>
<p>Es posible que haya sido eliminado.</p>
<a class="button" href="'.e(inicio()).'">Volver al inicio</a>
</div>'; pie(); exit;
}
function icono($name) {
    $paths = [
        'home'=>'<rect x="3" y="3" width="7" height="7" rx="1"/>
<rect x="14" y="3" width="7" height="7" rx="1"/>
<rect x="3" y="14" width="7" height="7" rx="1"/>
<rect x="14" y="14" width="7" height="7" rx="1"/>',
        'people'=>'<circle cx="9" cy="8" r="3"/>
<path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-4-5"/>',
        'book'=>'<path d="M12 5C8 2 4 3 2 4v16c4-2 7-1 10 1 3-2 6-3 10-1V4c-2-1-6-2-10 1Z M12 5v16"/>',
        'grade'=>'<rect x="5" y="3" width="14" height="18" rx="2"/>
<path d="M9 8h6M9 12h6M9 16h3"/>',
        'exit'=>'<path d="M9 4H4v16h5M13 7l5 5-5 5M8 12h10"/>',
        'arrow'=>'<path d="M5 12h14M13 6l6 6-6 6"/>',
        'plus'=>'<path d="M12 5v14M5 12h14"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['book']).'</svg>';
}
