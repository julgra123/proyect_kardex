<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('nota.ver');
$q=trim($_GET['q'] ?? ''); $materia=$_GET['materia'] ?? ''; $trimestre=$_GET['trimestre'] ?? ''; $curso=$_GET['curso'] ?? '';
$params=[]; $where=' WHERE 1=1';
if(rol()==='docente') { $where.=' AND m.id_docente=? AND EXISTS (SELECT 1 FROM materia_cursos mc WHERE mc.id_materia=k.id_materia AND mc.curso=e.curso)'; $params[]=$_SESSION['usuario_id']; }
if($q!=='') { $where.=' AND (e.nombres LIKE ? OR e.apellidos LIKE ? OR e.re LIKE ?)'; array_push($params,"%$q%","%$q%","%$q%"); }
if($materia!=='') { $where.=' AND k.id_materia=?'; $params[]=$materia; }
if($trimestre!=='') { $where.=' AND k.trimestre=?'; $params[]=$trimestre; }
if($curso!=='') { $where.=' AND e.curso=?'; $params[]=$curso; }
$filas=todos('SELECT k.*,e.nombres,e.apellidos,e.curso,m.nombre_materia,CONCAT(d.nombres," ",d.apellidos) AS docente FROM kardex k JOIN estudiantes e ON e.re=k.re_estudiante JOIN materias m ON m.id_materia=k.id_materia JOIN docentes d ON d.id_docente=k.id_docente'.$where.' ORDER BY k.id_kardex DESC',$params);
cabecera('Calificaciones','notas','Consulta, registra y corrige las notas por materia y trimestre.',(tiene_permiso('nota.crear') ? '<a class="button" href="registrar_nota.php">'.icono('plus').' Registrar nota</a>' : ''));
?>
<section class="panel">
<form class="toolbar" method="get">
<?php campo('q','Estudiante',$q,'search','placeholder="Nombre o R.E."'); ?>
<div class="field">
<label for="materia">Materia</label>
<select id="materia" name="materia">
<option value="">Todas</option>
<?php foreach((rol()==='docente' ? todos('SELECT * FROM materias WHERE id_docente=? ORDER BY nombre_materia',[$_SESSION['usuario_id']]) : todos('SELECT * FROM materias ORDER BY nombre_materia')) as $m): ?>
<option value="<?= $m['id_materia'] ?>"<?= seleccionado($materia,$m['id_materia']) ?>>
<?= e($m['nombre_materia']) ?>
</option>
<?php endforeach ?>
</select>
</div>
<div class="field">
<label for="trimestre">Trimestre</label>
<select id="trimestre" name="trimestre">
<option value="">Todos</option>
<?php foreach([1,2,3] as $t): ?>
<option value="<?= $t ?>"<?= seleccionado($trimestre,$t) ?>>
<?= $t ?>º trimestre</option>
<?php endforeach ?>
</select>
</div>
<div class="field">
<label for="curso">Curso</label>
<select name="curso" id="curso">
<option value="">Todos</option>
<?php foreach((rol()==='docente' ? todos('SELECT DISTINCT mc.curso FROM materia_cursos mc INNER JOIN materias m ON m.id_materia=mc.id_materia WHERE m.id_docente=? ORDER BY mc.curso', [$_SESSION['usuario_id']]) : todos('SELECT DISTINCT curso FROM estudiantes ORDER BY curso')) as $c): ?>
<option<?= seleccionado($curso,$c['curso']) ?>>
<?= e($c['curso']) ?>
</option>
<?php endforeach ?>
</select>
</div>
<button class="button secondary">Filtrar</button>
<a href="notas.php">Limpiar</a>
</form>
<div class="panel-head">
<h2>Registro de evaluaciones</h2>
<span class="badge neutral">
<?= count($filas) ?> registros</span>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Estudiante</th>
<th>Materia / Docente</th>
<th>Trimestre</th>
<th>Nota</th>
<?php if(tiene_permiso('nota.editar')): ?>
<th>Acciones</th>
<?php endif ?>
</tr>
</thead>
<tbody>
<?php foreach($filas as $f): ?>
<tr>
<td>
<div class="person">
<div>
<strong>
<a href="ver_kardex.php?re=<?= urlencode($f['re_estudiante']) ?>">
<?= e(nombre($f)) ?>
</a>
</strong>
<small>
<?= e($f['curso'].' · '.$f['re_estudiante']) ?>
</small>
</div>
</div>
</td>
<td>
<div class="person">
<div>
<strong>
<?= e($f['nombre_materia']) ?>
</strong>
<small>
<?= e($f['docente']) ?>
</small>
</div>
</div>
</td>
<td>
<?= $f['trimestre'] ?>º trimestre</td>
<td>
<span class="grade <?= $f['nota']>=51 ? 'good' : 'bad' ?>">
<?= number_format($f['nota'],2) ?>
</span>
</td>
<?php if(tiene_permiso('nota.editar')): ?>
<td>
<div class="actions">
<a class="button small secondary" href="editar_nota.php?id=<?= $f['id_kardex'] ?>">Editar</a>
<a class="danger-link" href="eliminar.php?tipo=nota&amp;id=<?= $f['id_kardex'] ?>">Eliminar</a>
</div>
</td>
<?php endif ?>
</tr>
<?php endforeach; if(!$filas) vacio(tiene_permiso('nota.editar') ? 5 : 4,'No hay calificaciones para mostrar con estos filtros.'); ?>
</tbody>
</table>
</div>
</section>
<?php pie(); ?>
