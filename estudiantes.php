<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('estudiante.ver');
$q = trim($_GET['q'] ?? ''); $curso = trim($_GET['curso'] ?? '');
$params = []; $where = ' WHERE 1=1';
if (rol() === 'docente') {
    $where .= ' AND EXISTS (SELECT 1 FROM materia_cursos mc INNER JOIN materias m ON m.id_materia=mc.id_materia WHERE mc.curso=e.curso AND m.id_docente=?)';
    $params[] = $_SESSION['usuario_id'];
}
if ($q !== '') { $where .= ' AND (e.re LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ?)'; array_push($params, "%$q%","%$q%","%$q%"); }
if ($curso !== '') { $where .= ' AND e.curso=?'; $params[] = $curso; }
$filas = todos("SELECT e.*, i.estado FROM estudiantes e LEFT JOIN identidades i ON i.rol='estudiante' AND i.ref_id=e.re".$where.' ORDER BY apellidos,nombres',$params);
cabecera('Estudiantes','estudiantes','Consulta las fichas y acompaña el recorrido de cada estudiante.',tiene_permiso('estudiante.crear') ? '<a class="button" href="nuevo_estudiante.php">'.icono('plus').' Nuevo estudiante</a>' : '');
?>
<section class="panel">
<form class="toolbar" method="get">
<?php campo('q','Buscar estudiante',$q,'search','placeholder="Nombre, apellido o R.E."'); ?>
<div class="field">
<label for="curso">Curso</label>
<select id="curso" name="curso">
<option value="">Todos los cursos</option>
<?php foreach((rol() === 'docente' ? todos('SELECT DISTINCT mc.curso FROM materia_cursos mc INNER JOIN materias m ON m.id_materia=mc.id_materia WHERE m.id_docente=? ORDER BY mc.curso', [$_SESSION['usuario_id']]) : todos('SELECT DISTINCT curso FROM estudiantes ORDER BY curso')) as $c): ?>
<option<?= seleccionado($curso,$c['curso']) ?>>
<?= e($c['curso']) ?>
</option>
<?php endforeach ?>
</select>
</div>
<button class="button secondary">Buscar</button>
<a href="estudiantes.php">Limpiar</a>
</form>
<div class="panel-head">
<h2>Directorio estudiantil</h2>
<span class="badge neutral">
<?= count($filas) ?> registros</span>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Estudiante</th>
<th>Curso</th>
<th>Nacimiento</th>
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
<small>
<?= e($f['re']) ?> <?= badge_estado($f['estado'] ?? 'activa') ?>
</small>
</div>
</div>
</td>
<td>
<span class="badge">
<?= e($f['curso']) ?>
</span>
</td>
<td>
<?= e(date('d/m/Y',strtotime($f['fecha_nacimiento']))) ?>
</td>
<td>
<div class="actions">
<a class="button small secondary" href="ver_kardex.php?re=<?= urlencode($f['re']) ?>">Ver kardex</a>
<?php if(tiene_permiso('estudiante.editar')): ?>
<a href="editar_estudiante.php?re=<?= urlencode($f['re']) ?>">Editar</a>
<?php if(($f['estado'] ?? 'activa') !== 'revocada'): ?><a class="danger-link" href="eliminar.php?tipo=estudiante&amp;id=<?= urlencode($f['re']) ?>">Dar de baja</a><?php endif ?>
<?php endif ?>
</div>
</td>
</tr>
<?php endforeach; if(!$filas) vacio(4,'Registra un estudiante o ajusta los filtros de búsqueda.'); ?>
</tbody>
</table>
</div>
</section>
<?php pie(); ?>
