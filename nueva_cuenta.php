<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('identidad.gestionar');
$d = ['ci'=>'','nombres'=>'','apellidos'=>'','usuario'=>'','rol'=>'auditor']; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['ci','nombres','apellidos','usuario','rol'] as $c) $d[$c] = post($c); $pw = (string)($_POST['password'] ?? '');
    foreach (['ci','nombres','apellidos','usuario'] as $c) if ($d[$c] === '' || longitud($d[$c]) > ($c === 'ci' ? 20 : 100)) $error = 'Completa todos los campos respetando su longitud máxima.';
    if (!$error && !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $d['usuario'])) $error = 'El usuario admite letras, números, punto, guion y guion bajo (3 a 50).';
    if (!$error && !in_array($d['rol'], ['admin','auditor'], true)) $error = 'Rol no válido.';
    if (!$error) $error = validar_password($pw, $d['usuario']);
    if (!$error && valor('SELECT COUNT(*) FROM identidades WHERE usuario=?', [$d['usuario']])) $error = 'Ese usuario ya existe.';
    if (!$error) exigir_mfa($error);
    if (!$error) {
        try {
            $pdo->beginTransaction();
            consulta("INSERT INTO administradores (ci,nombres,apellidos,usuario,password) VALUES (?,?,?,?,'!migrado')", [$d['ci'], $d['nombres'], $d['apellidos'], $d['usuario']]);
            $ref = $pdo->lastInsertId();
            crear_identidad($d['usuario'], nombre($d), $d['rol'], $ref, $pw, 1);
            $pdo->commit(); aviso('Cuenta creada. Deberá cambiar su contraseña en el primer ingreso.'); ir('identidades.php');
        } catch (PDOException $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); $error = errorBD($ex); }
          catch (Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log('crear_cuenta: '.$ex->getMessage()); $error = 'No se pudo crear la cuenta de forma segura. No se confirmó el cambio.'; }
    }
}
cabecera('Nueva cuenta administrativa', 'identidades', 'Crea una identidad única de administración o auditoría.');
?>
<a class="back-link" href="identidades.php">← Volver a identidades</a>
<section class="panel"><div class="panel-body">
<?php errorFormulario($error); ?>
<form method="post" autocomplete="off">
<?= csrf_campo() ?>
<div class="form-grid">
<?php campo('ci','Cédula de identidad',$d['ci'],'text','required maxlength="20"'); campo('nombres','Nombres',$d['nombres'],'text','required maxlength="100"'); campo('apellidos','Apellidos',$d['apellidos'],'text','required maxlength="100"'); campo('usuario','Usuario',$d['usuario'],'text','required maxlength="50" autocomplete="off"'); ?>
<div class="field"><label for="rol">Rol</label><select id="rol" name="rol"><option value="auditor"<?= seleccionado($d['rol'],'auditor') ?>>auditor (solo lectura)</option><option value="admin"<?= seleccionado($d['rol'],'admin') ?>>admin</option></select></div>
<?php campo('password','Contraseña inicial (temporal)','','password','required minlength="10" maxlength="128" autocomplete="new-password"'); ?>
<div class="full"><?= mfa_campo() ?></div>
</div>
<div class="form-actions"><button class="button" type="submit">Crear cuenta</button></div>
</form></div></section>
<?php pie(); ?>
