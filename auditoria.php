<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('auditoria.ver');
$evento = trim($_GET['evento'] ?? ''); $res = $_GET['resultado'] ?? ''; $sev = $_GET['severidad'] ?? ''; $usr = trim($_GET['usuario'] ?? '');
$desde = $_GET['desde'] ?? ''; $hasta = $_GET['hasta'] ?? '';
$where = ' WHERE 1=1'; $p = [];
if ($evento !== '') { $where .= ' AND evento LIKE ?'; $p[] = "%$evento%"; }
if (in_array($res, ['ok','fallo','denegado'], true)) { $where .= ' AND resultado=?'; $p[] = $res; }
if (in_array($sev, ['info','alerta','critica'], true)) { $where .= ' AND severidad=?'; $p[] = $sev; }
if ($usr !== '') { $where .= ' AND usuario LIKE ?'; $p[] = "%$usr%"; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) { $where .= ' AND momento>=?'; $p[] = $desde.' 00:00:00'; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) { $where .= ' AND momento<=?'; $p[] = $hasta.' 23:59:59'; }
$filas = todos('SELECT * FROM auditoria'.$where.' ORDER BY id DESC LIMIT 300', $p);
$resumen = todos("SELECT evento, COUNT(*) n FROM auditoria WHERE momento>? GROUP BY evento ORDER BY n DESC LIMIT 6", [date('Y-m-d H:i:s', time()-86400)]);
cabecera('Auditoría', 'auditoria', 'Registro inmutable (con cadena de hashes) de eventos de seguridad. Se muestran los 300 más recientes.');
?>
<section class="panel">
<form class="toolbar" method="get">
<?php campo('evento','Evento',$evento,'search','placeholder="login_fallido, rol_cambiado..."'); campo('usuario','Usuario',$usr,'search'); ?>
<div class="field"><label for="resultado">Resultado</label><select id="resultado" name="resultado"><option value="">Todos</option><?php foreach(['ok','fallo','denegado'] as $x): ?><option<?= seleccionado($res,$x) ?>><?= $x ?></option><?php endforeach ?></select></div>
<div class="field"><label for="severidad">Severidad</label><select id="severidad" name="severidad"><option value="">Todas</option><?php foreach(['info','alerta','critica'] as $x): ?><option<?= seleccionado($sev,$x) ?>><?= $x ?></option><?php endforeach ?></select></div>
<?php campo('desde','Desde',$desde,'date'); campo('hasta','Hasta',$hasta,'date'); ?>
<button class="button secondary">Filtrar</button><a href="auditoria.php">Limpiar</a>
</form>
<?php if($resumen): ?><div class="panel-body"><strong>Últimas 24 h:</strong> <?php foreach($resumen as $r): ?><span class="badge neutral"><?= e($r['evento']) ?> × <?= (int)$r['n'] ?></span> <?php endforeach ?></div><?php endif ?>
<div class="table-wrap"><table>
<thead><tr><th>#</th><th>Fecha</th><th>Usuario / rol</th><th>Evento</th><th>Resultado</th><th>Recurso</th><th>Detalle</th><th>IP</th></tr></thead><tbody>
<?php foreach($filas as $f): ?>
<tr><td><?= (int)$f['id'] ?></td><td><?= e($f['momento']) ?></td><td><?= e($f['usuario']) ?><br><small class="muted"><?= e($f['rol']) ?></small></td>
<td><strong><?= e($f['evento']) ?></strong><?= $f['severidad'] !== 'info' ? ' <span class="badge sev-'.e($f['severidad']).'">'.e($f['severidad']).'</span>' : '' ?></td>
<td><span class="badge <?= $f['resultado'] === 'ok' ? '' : 'bad' ?>"><?= e($f['resultado']) ?></span></td>
<td><?= e($f['recurso']) ?></td><td class="observation"><?= e($f['detalle']) ?></td><td><?= e($f['ip']) ?></td></tr>
<?php endforeach; if(!$filas) vacio(8,'No hay eventos con esos filtros.'); ?>
</tbody></table></div></section>
<?php pie(); ?>
