<?php
require_once 'includes/layout.php'; verificarSesion();
$yo = uno('SELECT * FROM identidades WHERE id_identidad=?', [$_SESSION['id_identidad']]);
$forzado = (bool)$yo['debe_cambiar_password']; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = (string)($_POST['actual'] ?? ''); $nueva = (string)($_POST['nueva'] ?? ''); $conf = (string)($_POST['confirmar'] ?? '');
    if (strlen($actual) > 128 || !password_verify($actual, $yo['password_hash'])) { $error = 'La contraseña actual no es correcta.'; auditar('password_cambio_fallido', 'fallo', '', '', 'alerta'); }
    elseif ($nueva !== $conf) $error = 'La confirmación no coincide con la nueva contraseña.';
    elseif ($nueva === $actual) $error = 'La nueva contraseña debe ser distinta de la actual.';
    elseif (($error = validar_password($nueva, $yo['usuario'])) === '') {
        try {
            $sesionActual = hash('sha256', session_id());
            $pdo->beginTransaction();
            consulta('UPDATE identidades SET password_hash=?, debe_cambiar_password=0 WHERE id_identidad=?', [hash_password($nueva), $yo['id_identidad']]);
            revocar_sesiones($yo['id_identidad'], 'cambio_password', $sesionActual);
            auditar('password_cambiada', 'ok', 'identidad#' . $yo['id_identidad'], '', 'info', null, null, null, true);
            $pdo->commit();
            rotar_sesion();
            aviso('Contraseña actualizada. Las demás sesiones de tu cuenta fueron cerradas.'); ir(inicio());
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('cambio_password: '.$ex->getMessage());
            $error = 'No se pudo actualizar la contraseña de forma segura. No se confirmó el cambio.';
        }
    }
}
cabecera('Mi contraseña', 'pass', $forzado ? 'Por seguridad debes definir una contraseña nueva antes de continuar.' : 'Actualiza tu contraseña de acceso.');
?>
<section class="panel"><div class="panel-body">
<?php errorFormulario($error); ?>
<form method="post">
<?= csrf_campo() ?>
<div class="form-grid">
<?php campo('actual', 'Contraseña actual', '', 'password', 'required maxlength="128" autocomplete="current-password"');
campo('nueva', 'Nueva contraseña', '', 'password', 'required minlength="10" maxlength="128" autocomplete="new-password"');
campo('confirmar', 'Confirmar nueva contraseña', '', 'password', 'required minlength="10" maxlength="128" autocomplete="new-password"'); ?>
</div>
<p class="muted">Mínimo 10 caracteres, con mayúsculas, minúsculas y números. No puede contener tu usuario.</p>
<div class="form-actions"><button class="button" type="submit">Guardar contraseña</button></div>
</form></div></section>
<?php pie(); ?>
