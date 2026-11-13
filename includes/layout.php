<?php
require_once __DIR__.'/funciones.php';
function cabecera($titulo, $activo = '', $subtitulo = '', $accion = '') {
    $pass = ['cambiar_password.php','Mi contraseña','home','pass'];
    $mapa = [
        'admin' => [['index.php','Resumen','home','inicio'], ['estudiantes.php','Estudiantes','people','estudiantes'], ['profesores.php','Docentes','people','docentes'], ['materias.php','Materias','book','materias'], ['notas.php','Calificaciones','grade','notas'], ['identidades.php','Identidades','people','identidades'], ['auditoria.php','Auditoría','grade','auditoria'], ['integridad.php','Integridad','book','integridad'], ['matriz.php','Matriz de permisos','grade','matriz'], ['configuracion.php','Configuración','home','config'], ['mfa_config.php','Mi seguridad (2FA)','home','mfa'], $pass],
        'auditor' => [['auditoria.php','Auditoría','grade','auditoria'], ['integridad.php','Integridad','book','integridad'], ['matriz.php','Matriz de permisos','grade','matriz'], ['estudiantes.php','Estudiantes','people','estudiantes'], ['notas.php','Calificaciones','grade','notas'], $pass],
        'docente' => [['mis_materias.php','Mis materias','book','materias'], ['estudiantes.php','Estudiantes','people','estudiantes'], ['notas.php','Calificaciones','grade','notas'], ['mfa_config.php','Mi seguridad (2FA)','home','mfa'], $pass],
        'coordinador' => [['estudiantes.php','Estudiantes','people','estudiantes'], ['notas.php','Calificaciones','grade','notas'], $pass],
        'estudiante' => [['mi_kardex.php','Mi kardex','grade','kardex'], $pass],
    ];
    $links = $mapa[rol()] ?? [];
    $rolNombre = ['admin'=>'Administración','auditor'=>'Auditoría','docente'=>'Docente','coordinador'=>'Coordinación','estudiante'=>'Estudiante'][rol()] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>
<?= e($titulo) ?> · Aula Kardex</title>
<link rel="stylesheet" href="assets/app.css">
<script src="assets/app.js" defer>
</script>
</head>
<body>
<aside class="sidebar">
<a class="brand" href="<?= e(inicio()) ?>">
<span class="brand-symbol">
<?= icono('book') ?>
</span>
<span>AULA<span class="brand-sub">KARDEX ESCOLAR</span>
</span>
</a>
<div class="nav-label">ESPACIO ACADÉMICO</div>
<nav aria-label="Navegación principal">
<?php foreach($links as [$href,$label,$icon,$key]): ?>
<a href="<?= $href ?>" class="nav-link <?= $activo === $key ? 'active' : '' ?>">
<?= icono($icon) ?>
<span>
<?= $label ?>
</span>
</a>
<?php endforeach ?>
</nav>
<div class="sidebar-bottom">
<div class="school-mark">Un espacio para<br>
<strong>aprender y crecer.</strong>
</div>
<a class="nav-link" href="logout.php">
<?= icono('exit') ?>Cerrar sesión</a>
</div>
</aside>
<div class="workspace">
<header class="topbar">
<span>Gestión académica <span class="divider">/</span> <?= e($titulo) ?>
</span>
<div class="identity">
<span class="avatar">
<?= e(inicial($_SESSION['nombre_completo'] ?? 'U')) ?>
</span>
<div>
<strong>
<?= e($_SESSION['nombre_completo'] ?? '') ?>
</strong>
<small>
<?= e($rolNombre) ?>
</small>
</div>
<a class="logout-top" href="logout.php">Cerrar sesión</a>
</div>
</header>
<main>
<div class="page-heading">
<div>
<p class="eyebrow">AULA · <?= e($rolNombre) ?>
</p>
<h1>
<?= e($titulo) ?>
</h1>
<?php if($subtitulo): ?>
<p class="subtitle">
<?= e($subtitulo) ?>
</p>
<?php endif ?>
</div>
<div class="heading-actions">
<?= $accion ?>
</div>
</div>
<?php if(isset($_SESSION['aviso'])): [$text,$type] = $_SESSION['aviso']; unset($_SESSION['aviso']); ?>
<div class="alert <?= e($type) ?>" role="status">
<?= e($text) ?>
</div>
<?php endif;
}
function pie() { ?>
<footer class="footer">
<span>AULA / Kardex escolar</span>
<span>Organización que acompaña el aprendizaje</span>
</footer>
</main>
</div>
</body>
</html>
<?php }
