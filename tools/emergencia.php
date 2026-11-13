<?php
// Habilita/deshabilita la cuenta de emergencia solo desde la consola del servidor.
//   php tools/emergencia.php activar "motivo del incidente"
//   php tools/emergencia.php desactivar "motivo"
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Solo línea de comandos.'); }
require_once __DIR__ . '/../includes/funciones.php';
$accion = $argv[1] ?? ''; $motivo = trim($argv[2] ?? '');
if (!in_array($accion, ['activar', 'desactivar'], true) || longitud($motivo) < 5 || longitud($motivo) > 255) {
    fwrite(STDERR, "Uso: php tools/emergencia.php activar|desactivar \"motivo (5-255 caracteres)\"\n"); exit(2);
}
$em = uno('SELECT * FROM identidades WHERE es_emergencia=1 LIMIT 1');
if (!$em) { fwrite(STDERR, "No existe cuenta de emergencia. Ejecuta tools/migrar_identidades.php\n"); exit(1); }
$quien = 'cli:' . (get_current_user() ?: 'administrador_sistema');
try {
    $pdo->beginTransaction();
    if ($accion === 'activar') {
        consulta("UPDATE identidades SET estado='activa', vigente_hasta=?, estado_cambiado_en=? WHERE id_identidad=?",
            [date('Y-m-d H:i:s', time() + 7200), ahora(), $em['id_identidad']]);
        auditar('emergencia_activada', 'ok', 'identidad#' . $em['id_identidad'], ['motivo'=>$motivo, 'vigencia'=>'2 horas', 'via'=>'consola'], 'critica', null, $quien, 'sistema', true);
        $mensaje = "Cuenta de emergencia habilitada por 2 horas.\n";
    } else {
        consulta("UPDATE identidades SET estado='suspendida', vigente_hasta=NULL, estado_cambiado_en=? WHERE id_identidad=?",
            [ahora(), $em['id_identidad']]);
        revocar_sesiones($em['id_identidad'], 'emergencia_desactivada');
        auditar('emergencia_desactivada', 'ok', 'identidad#' . $em['id_identidad'], ['motivo'=>$motivo, 'via'=>'consola'], 'critica', null, $quien, 'sistema', true);
        $mensaje = "Cuenta de emergencia desactivada y sesiones revocadas.\n";
    }
    $pdo->commit();
    echo $mensaje;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('emergencia: ' . $e->getMessage());
    fwrite(STDERR, "No se pudo completar la acción de emergencia ni su auditoría. No se confirmó el cambio.\n");
    exit(1);
}
