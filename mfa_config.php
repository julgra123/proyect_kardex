<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('mfa.configurar');
$yo = uno('SELECT * FROM identidades WHERE id_identidad=?', [$_SESSION['id_identidad']]);
$error = '';
if (!$yo['mfa_activo']) {
    if (empty($_SESSION['mfa_pendiente'])) $_SESSION['mfa_pendiente'] = totp_secreto_nuevo();
    $secreto = $_SESSION['mfa_pendiente'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $paso = totp_verificar($secreto, preg_replace('/\D/', '', post('codigo_mfa')), 0);
        if ($paso === false) { $error = 'El código no es correcto. Revisa la hora de tu teléfono e inténtalo de nuevo.'; auditar('mfa_fallido', 'fallo', 'activacion', '', 'alerta'); }
        else {
            try {
                $pdo->beginTransaction();
                consulta('UPDATE identidades SET mfa_secreto=?, mfa_activo=1, mfa_ultimo_paso=? WHERE id_identidad=?', [cifrar($secreto), $paso, $yo['id_identidad']]);
                auditar('mfa_activado', 'ok', 'identidad#' . $yo['id_identidad'], '', 'alerta', null, null, null, true);
                $pdo->commit();
                unset($_SESSION['mfa_pendiente']);
                aviso('Segundo factor activado correctamente.'); ir('mfa_config.php');
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('activar_mfa: '.$ex->getMessage());
                $error = 'No se pudo activar el segundo factor de forma segura. Inténtalo nuevamente.';
            }
        }
    }
}
cabecera('Mi seguridad (2FA)', 'mfa', 'Segundo factor para operaciones de alto riesgo (cambios de rol, bajas, restauraciones).');
?>
<section class="panel"><div class="panel-body">
<?php errorFormulario($error); if ($yo['mfa_activo']): ?>
<h2>Segundo factor activo</h2>
<p>Las operaciones sensibles te pedirán el código de 6 dígitos de tu aplicación autenticadora. El secreto está almacenado cifrado.</p>
<?php else: ?>
<h2>Activa tu aplicación autenticadora</h2>
<p>1) En Google Authenticator, Microsoft Authenticator o similar elige «Ingresar clave de configuración». 2) Usuario: <strong><?= e($yo['usuario']) ?></strong>. 3) Clave (tipo basado en tiempo):</p>
<div class="secret mono"><?= e(chunk_split($secreto, 4, ' ')) ?></div>
<form method="post">
<?= csrf_campo() ?>
<div class="form-grid"><?php campo('codigo_mfa', 'Código de 6 dígitos para confirmar', '', 'text', 'required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"'); ?></div>
<div class="form-actions"><button class="button" type="submit">Activar segundo factor</button></div>
</form>
<?php endif ?>
</div></section>
<?php pie(); ?>
