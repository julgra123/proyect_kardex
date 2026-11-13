<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('panel.ver'); $mfaOk = (bool)valor('SELECT mfa_activo FROM identidades WHERE id_identidad=?',[$_SESSION['id_identidad']]);
$estudiantes = valor('SELECT COUNT(*) FROM estudiantes'); $docentes = valor('SELECT COUNT(*) FROM docentes');
$materias = valor('SELECT COUNT(*) FROM materias'); $notas = valor('SELECT COUNT(*) FROM kardex');
$sinAsignar = valor('SELECT COUNT(*) FROM materias WHERE id_docente IS NULL');
$recientes = todos('SELECT k.*,e.nombres,e.apellidos,e.curso,m.nombre_materia FROM kardex k JOIN estudiantes e ON e.re=k.re_estudiante JOIN materias m ON m.id_materia=k.id_materia ORDER BY k.id_kardex DESC LIMIT 5');
cabecera('Resumen académico','inicio','Todo lo que necesitas para organizar el día a día del colegio.','<a class="button" href="registrar_nota.php">'.icono('plus').' Registrar nota</a>');
?>
<?php if(!$mfaOk): ?>
<div class="alert error">Aún no activaste tu segundo factor. Sin él no podrás cambiar roles, dar de baja cuentas ni restaurar registros. <a href="mfa_config.php">Activarlo ahora</a></div>
<?php endif ?>
<section class="hero">
<div>
<p class="eyebrow">EL APRENDIZAJE, EN UN SOLO LUGAR</p>
<h2>Una mirada clara al<br>progreso de tu colegio.</h2>
<p>Estudiantes, docentes y calificaciones conectados para acompañar cada etapa del aprendizaje.</p>
</div>
<div class="hero-art">
<?= icono('book') ?>
</div>
</section>
<div class="stats">
<?php foreach([['Estudiantes',$estudiantes,'Fichas académicas','people'],['Docentes',$docentes,'Equipo educativo','people'],['Materias',$materias,$sinAsignar.' sin docente asignado','book'],['Calificaciones',$notas,'Notas registradas','grade']] as [$label,$n,$detail,$ico]): ?>
<div class="stat">
<span class="stat-icon">
<?= icono($ico) ?>
</span>
<div class="label">
<?= $label ?>
</div>
<div class="number">
<?= $n ?>
</div>
<div class="detail">
<?= $detail ?>
</div>
</div>
<?php endforeach ?>
</div>
<div class="grid-two">
<section class="panel">
<div class="panel-head">
<div>
<h2>Últimas calificaciones</h2>
<p>Los registros incorporados recientemente</p>
</div>
<a href="notas.php">Ver todas →</a>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Estudiante / Materia</th>
<th>Trimestre</th>
<th>Nota</th>
</tr>
</thead>
<tbody>
<?php foreach($recientes as $r): ?>
<tr>
<td>
<div class="person">
<div>
<strong>
<a href="ver_kardex.php?re=<?= urlencode($r['re_estudiante']) ?>">
<?= e(nombre($r)) ?>
</a>
</strong>
<small>
<?= e($r['nombre_materia']) ?> · <?= e($r['curso']) ?>
</small>
</div>
</div>
</td>
<td>
<?= $r['trimestre'] ?>º trimestre</td>
<td>
<span class="badge <?= $r['nota']>=51 ? '' : 'bad' ?>">
<?= number_format($r['nota'],2) ?>
</span>
</td>
</tr>
<?php endforeach; if(!$recientes) vacio(3,'Registra una primera nota para verla aquí.'); ?>
</tbody>
</table>
</div>
</section>
<section class="panel">
<div class="panel-head">
<div>
<h2>Accesos rápidos</h2>
<p>Continúa con tus tareas habituales</p>
</div>
</div>
<?php foreach([['nuevo_estudiante.php','Registrar estudiante','Crea una ficha estudiantil completa','people'],['nuevo_profesor.php','Incorporar docente','Registra sus datos y cuenta de acceso','people'],['nueva_materia.php','Crear materia','Amplía el catálogo y asigna un docente','book'],['estudiantes.php','Consultar un kardex','Revisa el avance de un estudiante','grade'],['identidades.php','Identidades y roles','Estados, bajas y cambios de función','people'],['auditoria.php','Auditoría','Eventos de seguridad y accesos denegados','grade']] as [$href,$title,$desc,$ico]): ?>
<a class="quick-link" href="<?= $href ?>">
<span class="quick-icon">
<?= icono($ico) ?>
</span>
<span>
<strong>
<?= $title ?>
</strong>
<small>
<?= $desc ?>
</small>
</span>
<?= icono('arrow') ?>
</a>
<?php endforeach ?>
</section>
</div>
<?php pie(); ?>
