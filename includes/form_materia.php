<?php
require_once __DIR__.'/layout.php'; verificarSesion(); autorizar($editar ? 'materia.editar' : 'materia.crear');
$id = $_GET['id'] ?? '';
$datos = $editar ? uno('SELECT * FROM materias WHERE id_materia=?',[$id]) : ['nombre_materia'=>'','id_docente'=>''];
if (!$datos) noExiste('Materia');
$docentes = docentes_activos($editar ? ($datos['id_docente'] ?? null) : null);
$cursos = array_column(todos('SELECT DISTINCT curso FROM estudiantes ORDER BY curso'), 'curso');
$cursosSeleccionados = $editar ? array_column(todos('SELECT curso FROM materia_cursos WHERE id_materia=? ORDER BY curso', [$id]), 'curso') : [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre_materia'] = post('nombre_materia'); $datos['id_docente'] = post('id_docente');
    $cursosRecibidos = $_POST['cursos'] ?? [];
    $cursosSeleccionados = [];
    if (!is_array($cursosRecibidos)) $error = 'La selección de cursos no es válida.';
    else {
        foreach ($cursosRecibidos as $cursoRecibido) {
            if (!is_string($cursoRecibido) || longitud($cursoRecibido) > 50) { $error = 'La selección de cursos no es válida.'; break; }
            $cursosSeleccionados[] = $cursoRecibido;
        }
    }
    $cursosSeleccionados = array_values(array_unique($cursosSeleccionados));
    $cursosValidos = array_map('strval', $cursos);
    if ($datos['nombre_materia'] === '' || longitud($datos['nombre_materia']) > 100) $error = 'Ingresa el nombre de la materia (máximo 100 caracteres).';
    elseif (valor('SELECT COUNT(*) FROM materias WHERE nombre_materia=? AND id_materia<>?',[$datos['nombre_materia'],$editar ? $id : 0])) $error = 'Ya existe una materia con ese nombre.';
    elseif ($datos['id_docente'] !== '' && !in_array((string)$datos['id_docente'], array_map('strval', array_column($docentes,'id_docente')), true)) $error = 'Selecciona un docente activo y válido.';
    elseif (array_diff($cursosSeleccionados, $cursosValidos)) $error = 'Selecciona únicamente cursos existentes.';
    if (!$error && !exigir_mfa($error)) { /* MFA requerido para modificar asignaciones académicas. */ }
    if (!$error) try {
        $pdo->beginTransaction();
        $args = [$datos['nombre_materia'],$datos['id_docente'] !== '' ? $datos['id_docente'] : null];
        if ($editar) { $args[] = $id; consulta('UPDATE materias SET nombre_materia=?,id_docente=? WHERE id_materia=?',$args); }
        else consulta('INSERT INTO materias (nombre_materia,id_docente) VALUES (?,?)',$args);
        $mid = $editar ? $id : $pdo->lastInsertId();
        consulta('DELETE FROM materia_cursos WHERE id_materia=?', [$mid]);
        foreach ($cursosSeleccionados as $curso) consulta('INSERT INTO materia_cursos (id_materia,curso) VALUES (?,?)', [$mid, $curso]);
        auditar($editar ? 'materia_editada' : 'materia_creada', 'ok', 'materia#'.$mid, ['nombre'=>$datos['nombre_materia'], 'docente'=>$datos['id_docente'] !== '' ? $datos['id_docente'] : null, 'cursos'=>$cursosSeleccionados], 'info', null, null, null, true);
        $pdo->commit();
        aviso($editar ? 'Materia actualizada correctamente.' : 'Materia creada correctamente.'); ir('materias.php');
    } catch(PDOException $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); $error = errorBD($ex); }
      catch(Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log('guardar_materia: '.$ex->getMessage()); $error = 'No se pudo guardar la materia y su asignación de cursos de forma segura.'; }
}
cabecera($editar ? 'Editar materia' : 'Nueva materia','materias','Organiza la oferta académica y asigna a su docente responsable.');
?>
<a class="back-link" href="materias.php">← Volver a materias</a>
<div class="form-layout">
<section class="panel">
<div class="panel-head">
<h2>Información de la materia</h2>
</div>
<div class="panel-body">
<?php errorFormulario($error); ?>
<form method="post">
<?= csrf_campo() ?>
<div class="form-grid">
<div class="full">
<?php campo('nombre_materia','Nombre de la materia',$datos['nombre_materia'],'text','required maxlength="100" placeholder="Ej. Matemática"'); ?>
</div>
<div class="field full">
<label for="id_docente">Docente responsable</label>
<select id="id_docente" name="id_docente">
<option value="">Sin asignar por el momento</option>
<?php foreach($docentes as $d): ?>
<option value="<?= $d['id_docente'] ?>"<?= seleccionado($datos['id_docente'],$d['id_docente']) ?>>
<?= e(nombre($d)) ?>
</option>
<?php endforeach ?>
</select>
<small>Puedes asignarlo ahora o más adelante desde Editar.</small>
</div>
<div class="field full">
<label>Cursos a los que corresponde esta materia</label>
<?php if (!$cursos): ?><p class="muted">Primero registra estudiantes para que aparezcan cursos disponibles.</p><?php else: ?>
<div class="form-grid">
<?php foreach ($cursos as $curso): ?>
<label class="check-row"><input type="checkbox" name="cursos[]" value="<?= e($curso) ?>"<?= in_array((string)$curso, array_map('strval', $cursosSeleccionados), true) ? ' checked' : '' ?>> <?= e($curso) ?></label>
<?php endforeach ?>
</div>
<small>El docente solo podrá consultar y calificar estudiantes de los cursos asignados a esta materia.</small>
<?php endif ?>
</div>
</div>
<?= mfa_campo() ?>
<div class="form-actions">
<a class="button secondary" href="materias.php">Cancelar</a>
<button class="button" type="submit">
<?= $editar ? 'Guardar cambios' : 'Crear materia' ?>
</button>
</div>
</form>
</div>
</section>
<aside class="note-panel">
<h3>Materias y docentes</h3>
<p>Una materia tiene un docente responsable y puede asignarse a uno o varios cursos.</p>
<p>Cambiar al responsable no modifica el docente evaluador de las notas ya registradas.</p>
<a href="nuevo_profesor.php">Registrar un docente →</a>
</aside>
</div>
<?php pie(); ?>
