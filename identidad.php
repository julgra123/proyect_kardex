<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('identidad.gestionar');
$id = (int)($_GET['id'] ?? 0);
$i = uno('SELECT * FROM identidades WHERE id_identidad=?', [$id]); if (!$i) noExiste('Identidad');
$error = ''; $nueva = '';
$familia = ['admin'=>['admin','auditor'], 'auditor'=>['admin','auditor'], 'docente'=>['docente','coordinador'], 'coordinador'=>['docente','coordinador'], 'estudiante'=>['estudiante']][$i['rol']];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = post('accion'); $motivo = post('motivo'); $rolNuevo = post('rol_nuevo');
    autorizar($accion === 'cambiar_rol' ? 'identidad.cambiar_rol' : 'identidad.gestionar', ['id_identidad'=>$id]);
    $esAdminUnico = $i['rol'] === 'admin' && !$i['es_emergencia'] && otros_admin_activos($id) === 0;
    if (!in_array($accion, ['suspender','reactivar','revocar','cambiar_rol','reset_password','cerrar_sesiones'], true)) $error = 'Acción no válida.';
    elseif (longitud($motivo) < 5 || longitud($motivo) > 255) $error = 'Indica el motivo de la acción (entre 5 y 255 caracteres).';
    elseif ($i['estado'] === 'revocada') $error = 'Una identidad revocada es definitiva: no admite más cambios.';
    elseif (!exigir_mfa($error)) { /* $error ya fue llenado */ }
    elseif ($accion === 'suspender' && $i['estado'] !== 'activa') $error = 'La cuenta ya no está activa.';
    elseif ($accion === 'reactivar' && $i['estado'] !== 'suspendida') $error = 'Solo se puede reactivar una cuenta suspendida.';
    elseif (in_array($accion, ['suspender','revocar'], true) && $esAdminUnico) $error = 'No puedes dejar el sistema sin administradores activos.';
    elseif ($accion === 'cambiar_rol' && (!in_array($rolNuevo, $familia, true) || $rolNuevo === $i['rol'])) $error = 'Rol destino no permitido para este perfil (solo dentro de su familia: admin↔auditor, docente↔coordinador).';
    elseif ($accion === 'cambiar_rol' && $esAdminUnico) $error = 'No puedes degradar al único administrador activo.';
    else {
        if ($accion === 'suspender') {
            try { cambiar_estado_identidad($i, 'suspendida', $motivo); aviso('Cuenta suspendida y sesiones cerradas.'); }
            catch (Throwable $ex) { $error = 'No se pudo suspender la cuenta de forma segura. No se confirmó el cambio.'; }
        }
        elseif ($accion === 'reactivar') {
            try { cambiar_estado_identidad($i, 'activa', $motivo); aviso('Cuenta reactivada.'); }
            catch (Throwable $ex) { $error = 'No se pudo reactivar la cuenta de forma segura. No se confirmó el cambio.'; }
        }
        elseif ($accion === 'revocar') {
            try { cambiar_estado_identidad($i, 'revocada', $motivo); aviso('Cuenta revocada de forma definitiva. Sus registros históricos se conservaron.'); }
            catch (Throwable $ex) { $error = 'No se pudo revocar la cuenta de forma segura. No se confirmó el cambio.'; }
        }
        elseif ($accion === 'cerrar_sesiones') {
            $pdo->beginTransaction();
            try { revocar_sesiones($id, 'cierre_administrativo'); auditar('sesiones_revocadas', 'ok', 'identidad#'.$id, ['motivo'=>$motivo], 'alerta', null, null, null, true); $pdo->commit(); aviso('Todas las sesiones de la cuenta fueron cerradas.'); }
            catch (Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); $error = 'No se pudo cerrar las sesiones porque no fue posible completar la auditoría.'; }
        }
        elseif ($accion === 'reset_password') {
            $nueva = clave_temporal();
            $pdo->beginTransaction();
            try {
                consulta('UPDATE identidades SET password_hash=?, debe_cambiar_password=1 WHERE id_identidad=?', [hash_password($nueva), $id]);
                revocar_sesiones($id, 'reset_password');
                auditar('password_restablecida', 'ok', 'identidad#'.$id, ['motivo'=>$motivo], 'alerta', null, null, null, true);
                $pdo->commit();
                aviso('Contraseña temporal para '.$i['usuario'].' (mostrada una sola vez): '.$nueva);
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $nueva = ''; $error = 'No se pudo restablecer la contraseña porque no fue posible completar la auditoría.';
            }
        } elseif ($accion === 'cambiar_rol') {
            $liberadas = [];
            $pdo->beginTransaction();
            try {
                if ($rolNuevo === 'coordinador') { $liberadas = array_column(todos('SELECT id_materia FROM materias WHERE id_docente=?', [$i['ref_id']]), 'id_materia'); consulta('UPDATE materias SET id_docente=NULL WHERE id_docente=?', [$i['ref_id']]); }
                consulta('UPDATE identidades SET rol=? WHERE id_identidad=?', [$rolNuevo, $id]);
                consulta('INSERT INTO cambios_rol (id_identidad,rol_anterior,rol_nuevo,autorizado_por,motivo,momento) VALUES (?,?,?,?,?,?)', [$id, $i['rol'], $rolNuevo, $_SESSION['id_identidad'], $motivo, ahora()]);
                revocar_sesiones($id, 'cambio_rol');
                auditar('rol_cambiado', 'ok', 'identidad#'.$id, ['usuario'=>$i['usuario'], 'de'=>$i['rol'], 'a'=>$rolNuevo, 'motivo'=>$motivo, 'materias_liberadas'=>$liberadas], 'critica', null, null, null, true);
                $pdo->commit();
                aviso('Rol cambiado de '.$i['rol'].' a '.$rolNuevo.'. La cuenta deberá iniciar sesión de nuevo.');
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'No se pudo cambiar el rol porque no fue posible completar la operación y su auditoría.';
            }
        }
        if (!$error) ir('identidad.php?id='.$id);
    }
}
$cambios = todos('SELECT c.*, a.usuario AS autorizo FROM cambios_rol c LEFT JOIN identidades a ON a.id_identidad=c.autorizado_por WHERE c.id_identidad=? ORDER BY c.id DESC LIMIT 10', [$id]);
$eventos = todos('SELECT * FROM auditoria WHERE id_identidad=? OR recurso=? ORDER BY id DESC LIMIT 12', [$id, 'identidad#'.$id]);
$sesAct = (int)valor('SELECT COUNT(*) FROM sesiones WHERE id_identidad=? AND revocada=0 AND expira_en>?', [$id, ahora()]);
cabecera('Gestionar identidad', 'identidades', 'Todas las acciones exigen motivo, quedan auditadas y requieren tu segundo factor.');
?>
<a class="back-link" href="identidades.php">← Volver a identidades</a>
<div class="form-layout">
<section class="panel"><div class="panel-head"><div><h2><?= e($i['nombre_completo']) ?></h2><p>@<?= e($i['usuario']) ?> · rol <strong><?= e($i['rol']) ?></strong> · estado <strong><?= e($i['estado']) ?></strong> · sesiones activas: <?= $sesAct ?></p></div></div>
<div class="panel-body">
<?php errorFormulario($error); if ($i['estado'] === 'revocada'): ?>
<div class="alert info">Esta identidad fue revocada. Es definitiva; sus notas y registros históricos se conservan.</div>
<?php else: ?>
<form method="post">
<?= csrf_campo() ?>
<div class="form-grid">
<div class="field"><label for="accion">Acción</label><select id="accion" name="accion" required>
<?php foreach(['suspender'=>'Suspender (temporal)','reactivar'=>'Reactivar cuenta suspendida','revocar'=>'Revocar / dar de baja (definitivo)','cambiar_rol'=>'Cambiar de rol (función)','reset_password'=>'Restablecer contraseña','cerrar_sesiones'=>'Cerrar todas sus sesiones'] as $k=>$v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach ?></select></div>
<div class="field"><label for="rol_nuevo">Nuevo rol (solo para «Cambiar de rol»)</label><select id="rol_nuevo" name="rol_nuevo"><option value="">—</option>
<?php foreach($familia as $r): if ($r !== $i['rol']): ?><option value="<?= e($r) ?>"><?= e($r) ?></option><?php endif; endforeach ?></select></div>
<div class="field full"><label for="motivo">Motivo (queda en la auditoría)</label><input id="motivo" name="motivo" required minlength="5" maxlength="255" placeholder="Ej. Cuenta comprometida / cambio de función autorizado por dirección"></div>
<div class="full"><?= mfa_campo() ?></div>
</div>
<div class="form-actions"><button class="button danger" type="submit">Ejecutar acción</button></div>
</form>
<?php endif ?>
</div></section>
<aside class="note-panel"><h3>Reglas</h3><ul><li>Suspendida o revocada: pierde el acceso de inmediato.</li><li>Revocar un docente conserva sus notas y libera sus materias para reasignarlas.</li><li>No puedes modificar tu propia cuenta ni dejar al sistema sin administradores.</li><li>Cambio de rol: solo dentro de la familia (admin↔auditor, docente↔coordinador).</li></ul></aside>
</div>
<section class="panel"><div class="panel-head"><h2>Cambios de rol</h2></div><div class="table-wrap"><table>
<thead><tr><th>Fecha</th><th>De</th><th>A</th><th>Autorizó</th><th>Motivo</th></tr></thead><tbody>
<?php foreach($cambios as $c): ?><tr><td><?= e($c['momento']) ?></td><td><?= e($c['rol_anterior']) ?></td><td><?= e($c['rol_nuevo']) ?></td><td><?= e($c['autorizo']) ?></td><td><?= e($c['motivo']) ?></td></tr><?php endforeach; if(!$cambios) vacio(5,'Sin cambios de rol registrados.'); ?>
</tbody></table></div></section>
<section class="panel"><div class="panel-head"><h2>Últimos eventos de esta cuenta</h2></div><div class="table-wrap"><table>
<thead><tr><th>Fecha</th><th>Evento</th><th>Resultado</th><th>Detalle</th></tr></thead><tbody>
<?php foreach($eventos as $ev): ?><tr><td><?= e($ev['momento']) ?></td><td><?= e($ev['evento']) ?></td><td><?= e($ev['resultado']) ?></td><td class="observation"><?= e($ev['detalle']) ?></td></tr><?php endforeach; if(!$eventos) vacio(4,'Sin eventos.'); ?>
</tbody></table></div></section>
<?php pie(); ?>
