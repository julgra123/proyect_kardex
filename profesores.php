<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('docente.ver');
$q = trim($_GET['q'] ?? ''); $term = "%$q%";
$filas = todos("SELECT d.*, i.estado, i.rol AS rol_cuenta, (SELECT COUNT(*) FROM materias m WHERE m.id_docente=d.id_docente) AS cantidad FROM docentes d LEFT JOIN identidades i ON i.rol IN ('docente','coordinador') AND i.ref_id=d.id_docente WHERE d.nombres LIKE ? OR d.apellidos LIKE ? OR d.ci LIKE ? OR d.usuario LIKE ? ORDER BY d.apellidos,d.nombres",[$term,$term,$term,$term]);
cabecera('Docentes','docentes','Gestiona las fichas docentes y sus asignaciones académicas.','<a class="button" href="nuevo_profesor.php">'.icono('plus').' Nuevo docente</a>');
?>
<section class="panel">
<form class="toolbar" method="get">
<?php campo('q','Buscar docente',$q,'search','placeholder="Nombre, CI o usuario"'); ?>
<button class="button secondary">Buscar</button>
<a href="profesores.php">Limpiar</a>
</form>
<div class="panel-head">
<h2>Equipo docente</h2>
<span class="badge neutral">
<?= count($filas) ?> registros</span>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Docente</th>
<th>CI</th>
<th>Materias</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($filas as $f): ?>
<tr>
<td>
<div class="person">
<span class="avatar">
<?= e(inicial($f['nombres'])) ?>
</span>
<div>
<strong>
<?= e(nombre($f)) ?>
</strong>
<small>@<?= e($f['usuario']) ?> <?= e($f['rol_cuenta'] ?? '') ?> <?= badge_estado($f['estado'] ?? 'activa') ?>
</small>
</div>
</div>
</td>
<td>
<?= e($f['ci']) ?>
</td>
<td>
<a class="badge" href="materias.php?docente=<?= $f['id_docente'] ?>">
<?= $f['cantidad'] ?> asignadas</a>
</td>
<td>
<div class="actions">
<a class="button small secondary" href="editar_profesor.php?id=<?= $f['id_docente'] ?>">Editar</a>
<?php if(($f['estado'] ?? 'activa') !== 'revocada'): ?><a class="danger-link" href="eliminar.php?tipo=docente&amp;id=<?= $f['id_docente'] ?>">Dar de baja</a><?php endif ?>
</div>
</td>
</tr>
<?php endforeach; if(!$filas) vacio(4,'Registra un docente para empezar a asignar materias.'); ?>
</tbody>
</table>
</div>
</section>
<?php pie(); ?>
