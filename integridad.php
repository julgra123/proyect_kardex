<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('integridad.verificar');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    autorizar('integridad.restaurar');
    $idk = (int)post('id_kardex'); $motivo = post('motivo');
    if (longitud($motivo) < 5) $error = 'Indica el motivo de la restauración.';
    elseif (exigir_mfa($error)) {
        $error = restaurar_nota($idk);
        if ($error === '') { aviso('Calificación #'.$idk.' restaurada desde el historial.'); ir('integridad.php'); }
    }
}
[$totalAud, $falloAud] = verificar_cadena_auditoria();
$alteradas = []; $sinSello = 0;
foreach (todos('SELECT * FROM kardex ORDER BY id_kardex') as $k) {
    if ($k['sello'] === null || $k['sello'] === '') { $sinSello++; continue; }
    if (!hash_equals($k['sello'], sello_nota($k))) $alteradas[] = $k;
}
$eliminadas = todos("SELECT h.* FROM kardex_historial h WHERE h.accion='eliminar' AND NOT EXISTS (SELECT 1 FROM kardex k WHERE k.id_kardex=h.id_kardex) ORDER BY h.id DESC LIMIT 50");
auditar('integridad_verificada', 'ok', '', ['cadena_ok'=>$falloAud === null, 'alteradas'=>count($alteradas), 'eliminadas'=>count($eliminadas)], count($alteradas) || $falloAud ? 'critica' : 'info');
cabecera('Integridad', 'integridad', 'Detecta alteraciones no autorizadas de expedientes o de la auditoría y permite restaurar.');
$puedeRestaurar = tiene_permiso('integridad.restaurar');
?>
<section class="panel"><div class="panel-body"><?php errorFormulario($error); ?>
<h2>Cadena de auditoría</h2>
<?php if ($falloAud === null): ?><div class="alert">Íntegra: <?= (int)$totalAud ?> eventos verificados.</div>
<?php else: ?><div class="alert error">Cadena ROTA en el evento #<?= (int)$falloAud ?>: los registros fueron alterados o eliminados. Activa el procedimiento de incidente.</div><?php endif ?>
<h2>Sellos del kardex</h2>
<?php if (!$alteradas): ?><div class="alert">Ninguna calificación sellada fue alterada fuera del sistema.<?= $sinSello ? ' ('.$sinSello.' sin sello: ejecuta tools/migrar_identidades.php)' : '' ?></div>
<?php else: ?><div class="alert error"><?= count($alteradas) ?> calificaciones no coinciden con su sello (modificadas directamente en la base de datos).</div><?php endif ?>
</div></section>
<?php foreach ([['Calificaciones alteradas', $alteradas, 'k'], ['Calificaciones eliminadas (recuperables)', $eliminadas, 'h']] as [$tit, $lista, $t]): ?>
<?php if ($lista): ?>
<section class="panel"><div class="panel-head"><h2><?= e($tit) ?></h2></div><div class="table-wrap"><table>
<thead><tr><th>ID</th><th>Datos</th><?php if($puedeRestaurar): ?><th>Restaurar</th><?php endif ?></tr></thead><tbody>
<?php foreach ($lista as $r): $idk = (int)$r['id_kardex']; ?>
<tr><td>#<?= $idk ?></td><td class="observation"><?= $t === 'k' ? e($r['re_estudiante'].' · materia '.$r['id_materia'].' · trim '.$r['trimestre'].' · nota '.$r['nota']) : e($r['datos_antes']) ?></td>
<?php if($puedeRestaurar): ?><td><form method="post"><?= csrf_campo() ?><input type="hidden" name="id_kardex" value="<?= $idk ?>">
<input name="motivo" required minlength="5" maxlength="255" placeholder="Motivo" aria-label="Motivo"> <?= mfa_campo() ?> <button class="button small danger" type="submit">Restaurar</button></form></td><?php endif ?></tr>
<?php endforeach ?></tbody></table></div></section>
<?php endif; endforeach; ?>
<?php pie(); ?>
