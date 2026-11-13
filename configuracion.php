<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('config.cambiar');
$claves = ['max_intentos'=>['Intentos fallidos antes de bloquear',3,10], 'bloqueo_minutos'=>['Minutos de bloqueo / ventana de intentos',1,240], 'inactividad_minutos'=>['Minutos de inactividad para expirar la sesión',5,120], 'sesion_maxima_horas'=>['Duración máxima de una sesión (horas)',1,24]];
$modos = ['normal'=>'Normal', 'solo_lectura'=>'Solo lectura (se bloquean modificaciones académicas)', 'bloqueado'=>'Bloqueado (solo la cuenta de emergencia puede ingresar)'];
$emer = uno('SELECT * FROM identidades WHERE es_emergencia=1 LIMIT 1');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = post('accion'); $motivo = post('motivo');
    if (longitud($motivo) < 5 || longitud($motivo) > 255) $error = 'Indica el motivo del cambio (5 a 255 caracteres).';
    elseif (!exigir_mfa($error)) { }
    elseif ($accion === 'config') {
        $nuevo = []; foreach ($claves as $k => [$l, $mn, $mx]) { $v = (int)post($k); if ($v < $mn || $v > $mx) $error = "Valor fuera de rango en «$l» ($mn a $mx)."; $nuevo[$k] = (string)$v; }
        $nuevo['modo_sistema'] = post('modo_sistema'); if (!isset($modos[$nuevo['modo_sistema']])) $error = 'Modo de sistema no válido.';
        if (!$error) {
            $pdo->beginTransaction();
            try {
                foreach ($nuevo as $k => $v) {
                    $antes = (string)cfg($k, '');
                    if ($antes !== $v) {
                        consulta('REPLACE INTO configuracion (clave,valor) VALUES (?,?)', [$k, $v]);
                        auditar('config_cambiada', 'ok', $k, ['antes'=>$antes, 'despues'=>$v, 'motivo'=>$motivo], $k === 'modo_sistema' ? 'critica' : 'alerta', null, null, null, true);
                    }
                }
                $pdo->commit();
                unset($GLOBALS['_cfg']);
                aviso('Configuración guardada.'); ir('configuracion.php');
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'No se pudo guardar la configuración de forma segura. No se confirmó el cambio.';
            }
        }
    } elseif (in_array($accion, ['emergencia_on', 'emergencia_off'], true) && $emer) {
        $pdo->beginTransaction();
        try {
            if ($accion === 'emergencia_on') {
                consulta("UPDATE identidades SET estado='activa', vigente_hasta=?, estado_cambiado_en=? WHERE id_identidad=?", [date('Y-m-d H:i:s', time() + 7200), ahora(), $emer['id_identidad']]);
                auditar('emergencia_activada', 'ok', 'identidad#'.$emer['id_identidad'], ['motivo'=>$motivo, 'vigencia'=>'2 horas'], 'critica', null, null, null, true);
                aviso('Cuenta de emergencia habilitada por 2 horas.');
            } else {
                consulta("UPDATE identidades SET estado='suspendida', vigente_hasta=NULL, estado_cambiado_en=? WHERE id_identidad=?", [ahora(), $emer['id_identidad']]);
                revocar_sesiones($emer['id_identidad'], 'emergencia_desactivada');
                auditar('emergencia_desactivada', 'ok', 'identidad#'.$emer['id_identidad'], ['motivo'=>$motivo], 'critica', null, null, null, true);
                aviso('Cuenta de emergencia desactivada y sus sesiones cerradas.');
            }
            $pdo->commit();
            ir('configuracion.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'No se pudo modificar la cuenta de emergencia de forma segura. No se confirmó el cambio.';
        }
    } else $error = 'Acción no válida.';
}
cabecera('Configuración', 'config', 'Parámetros de seguridad, modo de contingencia y cuenta de emergencia. Todo cambio queda auditado.');
?>
<section class="panel"><div class="panel-body"><?php errorFormulario($error); ?>
<h2>Parámetros de seguridad</h2>
<form method="post"><?= csrf_campo() ?><input type="hidden" name="accion" value="config">
<div class="form-grid">
<?php foreach ($claves as $k => [$l, $mn, $mx]) campo($k, $l, cfg($k), 'number', 'required min="'.$mn.'" max="'.$mx.'"'); ?>
<div class="field full"><label for="modo_sistema">Modo del sistema</label><select id="modo_sistema" name="modo_sistema"><?php foreach($modos as $k=>$v): ?><option value="<?= $k ?>"<?= seleccionado(cfg('modo_sistema','normal'),$k) ?>><?= e($v) ?></option><?php endforeach ?></select></div>
<div class="field full"><label for="motivo">Motivo del cambio</label><input id="motivo" name="motivo" required minlength="5" maxlength="255"></div>
<div class="full"><?= mfa_campo() ?></div>
</div>
<div class="form-actions"><button class="button" type="submit">Guardar configuración</button></div></form>
</div></section>
<?php if ($emer): ?>
<section class="panel"><div class="panel-body">
<h2>Cuenta de emergencia («<?= e($emer['usuario']) ?>»)</h2>
<p>Estado: <strong><?= e($emer['estado']) ?></strong><?= $emer['vigente_hasta'] ? ' · vigente hasta '.e($emer['vigente_hasta']) : '' ?>. Se mantiene suspendida; su contraseña se guarda aparte (sobre sellado). Cada uso queda registrado como evento crítico.</p>
<form method="post"><?= csrf_campo() ?>
<div class="form-grid"><div class="field"><label for="accion2">Acción</label><select id="accion2" name="accion"><option value="emergencia_on">Habilitar por 2 horas</option><option value="emergencia_off">Desactivar ahora</option></select></div>
<div class="field"><label for="motivo2">Motivo</label><input id="motivo2" name="motivo" required minlength="5" maxlength="255"></div>
<div class="full"><?= mfa_campo() ?></div></div>
<div class="form-actions"><button class="button danger" type="submit">Aplicar</button></div></form>
</div></section>
<?php endif; pie(); ?>
