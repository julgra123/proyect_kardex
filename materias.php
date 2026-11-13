<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('materia.ver');
$q = trim($_GET['q'] ?? ''); $docente = $_GET['docente'] ?? ''; $params = ["%$q%"];
$sql = 'SELECT m.*,CONCAT(d.nombres," ",d.apellidos) AS docente,(SELECT COUNT(*) FROM kardex k WHERE k.id_materia=m.id_materia) AS notas,(SELECT GROUP_CONCAT(mc.curso ORDER BY mc.curso SEPARATOR ", ") FROM materia_cursos mc WHERE mc.id_materia=m.id_materia) AS cursos FROM materias m LEFT JOIN docentes d ON d.id_docente=m.id_docente WHERE m.nombre_materia LIKE ?';
if ($docente !== '') { $sql .= ' AND m.id_docente=?'; $params[] = $docente; }
$filas = todos($sql.' ORDER BY m.nombre_materia',$params);
cabecera('Materias','materias','Construye el catálogo de materias y organiza a sus responsables.','<a class="button" href="nueva_materia.php">'.icono('plus').' Nueva materia</a>');
?>
<section class="panel">
<form class="toolbar" method="get">
<?php campo('q','Buscar materia',$q,'search','placeholder="Nombre de la materia"'); ?>
<div class="field">
<label for="docente">Docente</label>
<select id="docente" name="docente">
<option value="">Todos los docentes</option>
<?php foreach(todos("SELECT d.* FROM docentes d LEFT JOIN identidades i ON i.rol IN ('docente','coordinador') AND i.ref_id=d.id_docente WHERE i.estado='activa' OR i.id_identidad IS NULL ORDER BY d.apellidos") as $d): ?>
<option value="<?= $d['id_docente'] ?>"<?= seleccionado($docente,$d['id_docente']) ?>>
<?= e(nombre($d)) ?>
</option>
<?php endforeach ?>
</select>
</div>
<button class="button secondary">Buscar</button>
<a href="materias.php">Limpiar</a>
</form>
<div class="panel-head">
<h2>Catálogo académico</h2>
<span class="badge neutral">
<?= count($filas) ?> materias</span>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Materia</th>
<th>Responsable</th>
<th>Cursos asignados</th>
<th>Calificaciones</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($filas as $f): ?>
<tr>
<td>
<strong>
<?= e($f['nombre_materia']) ?>
</strong>
<br>
<small class="muted">MAT-<?= str_pad($f['id_materia'],3,'0',STR_PAD_LEFT) ?>
</small>
</td>
<td>
<?= $f['docente'] ? e($f['docente']) : '<span class="badge warn">Sin asignar</span>' ?>
</td>
<td><?= $f['cursos'] ? e($f['cursos']) : '<span class="muted">Sin cursos</span>' ?></td>
<td>
<a href="notas.php?materia=<?= $f['id_materia'] ?>">
<?= $f['notas'] ?> registros</a>
</td>
<td>
<div class="actions">
<a class="button small secondary" href="editar_materia.php?id=<?= $f['id_materia'] ?>">Editar / asignar</a>
<a class="danger-link" href="eliminar.php?tipo=materia&amp;id=<?= $f['id_materia'] ?>">Eliminar</a>
</div>
</td>
</tr>
<?php endforeach; if(!$filas) vacio(5,'Crea una materia para comenzar a registrar calificaciones.'); ?>
</tbody>
</table>
</div>
</section>
<?php pie(); ?>
