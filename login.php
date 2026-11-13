<?php
require_once 'includes/funciones.php';
if (isset($_SESSION['usuario_id'])) ir(inicio());
$error = ''; $usuario = '';
$infos = ['inactividad'=>'Tu sesión se cerró por inactividad.', 'expirada'=>'Tu sesión expiró. Ingresa nuevamente.', 'revocada'=>'Tu sesión fue cerrada por seguridad.', 'cuenta'=>'No se pudo continuar con esa sesión. Ingresa nuevamente.', 'logout'=>'Cerraste sesión correctamente.', 'clave'=>'Contraseña actualizada. Ingresa con la nueva clave.'];
$info = $infos[$_GET['m'] ?? ''] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $usuario = post('usuario');
    [$ok, $msg] = intentar_login($usuario, (string)($_POST['password'] ?? ''));
    if ($ok) ir(inicio());
    $error = $msg;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ingreso · Aula Kardex</title>
<link rel="stylesheet" href="assets/app.css">
</head>
<body class="login-page">
<section class="login-story">
<div>
<a class="brand" href="login.php">
<span class="brand-symbol">
<?= icono('book') ?>
</span>
<span>AULA<span class="brand-sub">KARDEX ESCOLAR</span>
</span>
</a>
</div>
<div>
<div class="hero-art">
<?= icono('book') ?>
</div>
<h1>Cada aprendizaje<br>deja una <em>huella.</em>
</h1>
<p>Un espacio para organizar la vida académica y acompañar el progreso de cada estudiante.</p>
</div>
<small>ESTUDIANTES · DOCENTES · COMUNIDAD EDUCATIVA</small>
</section>
<section class="login-form-area">
<div class="login-box">
<p class="eyebrow">BIENVENIDO A TU ESPACIO</p>
<h2>Ingresar al kardex</h2>
<p>Consulta y administra la información académica de tu colegio.</p>
<?php errorFormulario($error); if ($info) echo '<div class="alert info" role="status">'.e($info).'</div>'; ?>
<form method="post" autocomplete="off">
<?= csrf_campo() ?>
<?php campo('usuario','Usuario o registro estudiantil',$usuario,'text','required maxlength="50" autocomplete="username" placeholder="Ingresa tu usuario o R.E."'); campo('password','Contraseña','','password','required maxlength="128" autocomplete="current-password"'); ?>
<button class="button" type="submit">Ingresar <?= icono('arrow') ?>
</button>
</form>
<div class="login-hint">
<strong>¿Eres estudiante?</strong>
<br>Ingresa con tu registro estudiantil y la contraseña que te entregó el colegio.</div>
</div>
</section>
</body>
</html>
