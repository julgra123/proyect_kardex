<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('identidad.gestionar');
$q = trim($_GET['q'] ?? ''); $estado = $_GET['estado'] ?? ''; $rolF = $_GET['rol'] ?? '';
$where = ' WHERE 1=1'; $params = [];
if ($q !== '') { $where .= ' AND (i.usuario LIKE ? OR i.nombre_completo LIKE ?)'; array_push($params, "%$q%", "%$q%"); }
if (in_array($estado, ['activa','suspendida','revocada'], true)) { $where .= ' AND i.estado=?'; $params[] = $estado; }
if (in_array($rolF, ['admin','auditor','docente','coordinador','estudiante'], true)) { $where .= ' AND i.rol=?'; $params[] = $rolF; }
$filas = todos('SELECT i.*, (SELECT COUNT(*) FROM sesiones s WHERE s.id_identidad=i.id_identidad AND s.revocada=0 AND s.expira_en>?) AS sesiones_activas FROM identidades i'.$where.' ORDER BY i.rol,i.usuario LIMIT 500', array_merge([ahora()], $params));
cabecera('Identidades', 'identidades', 'Cuentas únicas con estado: activa, suspendida o revocada.', '<a class="button" href="nueva_cuenta.php">'.icono('plus').' Nueva cuenta administrativa</a>');
?>
<section class="panel">
<form class="toolbar" method="get">
<?php campo('q', 'Buscar', $q, 'search', 'placeholder="Usuario o nombre"'); ?>
<div class="field"><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option>
<?php foreach(['activa','suspendida','revocada'] as $x): ?><option value="<?= $x ?>"<?= seleccionado($estado,$x) ?>><?= $x ?></option><?php endforeach ?></select></div>
<div class="field"><label for="rol">Rol</label><select id="rol" name="rol"><option value="">Todos</option>
<?php foreach(['admin','auditor','docente','coordinador','estudiante'] as $x): ?><option value="<?= $x ?>"<?= seleccionado($rolF,$x) ?>><?= $x ?></option><?php endforeach ?></select></div>
<button class="button secondary">Filtrar</button><a href="identidades.php">Limpiar</a>
</form>
<div class="panel-head"><h2>Registro de identidades</h2><span class="badge neutral"><?= count($filas) ?> cuentas</span></div>
<div class="table-wrap"><table>
<thead><tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Estado</th><th>2FA</th><th>Último acceso</th><th>Sesiones</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach($filas as $f): ?>
<tr>
<td><strong><?= e($f['usuario']) ?></strong><?= $f['es_emergencia'] ? ' <span class="badge warn">emergencia</span>' : '' ?></td>
<td><?= e($f['nombre_completo']) ?></td>
<td><span class="badge neutral"><?= e($f['rol']) ?></span></td>
<td><?= $f['estado'] === 'activa' ? '<span class="badge">Activa</span>' : badge_estado($f['estado']) ?></td>
<td><?= $f['mfa_activo'] ? 'Sí' : '<span class="muted">No</span>' ?></td>
<td><?= $f['ultimo_login'] ? e(date('d/m/Y H:i', strtotime($f['ultimo_login']))) : '<span class="muted">Nunca</span>' ?></td>
<td><?= (int)$f['sesiones_activas'] ?></td>
<td><div class="actions"><a class="button small secondary" href="identidad.php?id=<?= (int)$f['id_identidad'] ?>">Gestionar</a></div></td>
</tr>
<?php endforeach; if(!$filas) vacio(8,'No hay identidades con esos filtros.'); ?>
</tbody></table></div>
</section>
<?php pie(); ?>
