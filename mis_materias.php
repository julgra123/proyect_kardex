<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('materia.propias');
$filas = todos("SELECT m.*,(SELECT COUNT(*) FROM kardex k WHERE k.id_materia=m.id_materia) AS notas,(SELECT GROUP_CONCAT(mc.curso ORDER BY mc.curso SEPARATOR ', ') FROM materia_cursos mc WHERE mc.id_materia=m.id_materia) AS cursos FROM materias m WHERE id_docente=? ORDER BY nombre_materia",[$_SESSION['usuario_id']]);
cabecera('Mis materias','materias','Tu espacio para acompañar el aprendizaje y registrar calificaciones.');
?>
<div class="cards">
<?php foreach($filas as $m): ?>
<section class="panel subject-card">
<span class="subject-icon">
<?= icono('book') ?>
</span>
<p class="eyebrow">MAT-<?= str_pad($m['id_materia'],3,'0',STR_PAD_LEFT) ?>
</p>
<h2>
<?= e($m['nombre_materia']) ?>
</h2>
<p><?= $m['cursos'] ? 'Cursos: '.e($m['cursos']) : 'Sin cursos asignados' ?></p>
<p><?= $m['notas'] ?> calificaciones registradas</p>
<div class="actions">
<a class="button small" href="registrar_nota.php?materia=<?= $m['id_materia'] ?>">Registrar nota</a>
<a href="notas.php?materia=<?= $m['id_materia'] ?>">Ver notas →</a>
</div>
</section>
<?php endforeach ?>
</div>
<?php if(!$filas): ?>
<section class="panel empty">
<h2>Todavía no tienes materias asignadas</h2>
<p class="muted">El administrador puede asignártelas desde el módulo Materias.</p>
</section>
<?php endif; pie(); ?>
