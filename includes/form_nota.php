<?php
require_once __DIR__.'/layout.php'; verificarSesion(); autorizar($editar ? 'nota.editar' : 'nota.crear');
$id = $_GET['id'] ?? '';
$datos = $editar ? uno('SELECT * FROM kardex WHERE id_kardex=?',[$id]) : ['re_estudiante'=>$_GET['re'] ?? '', 'id_materia'=>$_GET['materia'] ?? '', 'id_docente'=>'', 'trimestre'=>'1','nota'=>'','observacion'=>''];
if (!$datos) noExiste('Calificación');
if ($editar) autorizar('nota.editar',['id_materia'=>$datos['id_materia'], 're_estudiante'=>$datos['re_estudiante']]);
$antes = $editar ? $datos : null;
$estudiantes = rol() === 'docente'
    ? todos('SELECT DISTINCT e.* FROM estudiantes e INNER JOIN materia_cursos mc ON mc.curso=e.curso INNER JOIN materias m ON m.id_materia=mc.id_materia WHERE m.id_docente=? ORDER BY e.apellidos,e.nombres', [$_SESSION['usuario_id']])
    : todos('SELECT * FROM estudiantes ORDER BY apellidos,nombres');
$materias = rol() === 'docente' ? todos('SELECT * FROM materias WHERE id_docente=? ORDER BY nombre_materia',[$_SESSION['usuario_id']]) : todos('SELECT * FROM materias ORDER BY nombre_materia');
$docentes = docentes_activos($editar ? ($datos['id_docente'] ?? null) : null);
// El docente solo puede figurar como evaluador él mismo.
if (rol()==='docente') $docentes = array_values(array_filter($docentes, fn($d)=>(string)$d['id_docente']===(string)$_SESSION['usuario_id']));
// Mantener visible una calificación histórica aunque se haya reasignado su materia.
if ($editar && rol()==='docente' && !in_array($datos['id_materia'],array_column($materias,'id_materia'))) {
    $historica = uno('SELECT * FROM materias WHERE id_materia=?',[$datos['id_materia']]); if($historica) $materias[] = $historica;
}
if (!$editar && $datos['id_materia']) {
    $m = uno('SELECT id_docente FROM materias WHERE id_materia=?',[$datos['id_materia']]);
    $datos['id_docente'] = $m['id_docente'] ?? '';
}
if (rol()==='docente' && !$editar) $datos['id_docente'] = $_SESSION['usuario_id'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach(['re_estudiante','id_materia','id_docente','trimestre','nota','observacion'] as $c) $datos[$c] = post($c);
    if (rol()==='docente') $datos['id_docente'] = (string)$_SESSION['usuario_id'];
    if (!uno('SELECT re FROM estudiantes WHERE re=?',[$datos['re_estudiante']])) $error = 'Selecciona un estudiante válido.';
    elseif (!uno('SELECT id_materia FROM materias WHERE id_materia=?',[$datos['id_materia']])) $error = 'Selecciona una materia válida.';
    elseif (!autorizar($editar ? 'nota.editar' : 'nota.crear',['id_materia'=>$datos['id_materia'], 're_estudiante'=>$datos['re_estudiante']],true)) $error = 'Solo puedes registrar o modificar notas en cursos asignados a tu materia.';
    elseif (!uno('SELECT id_docente FROM docentes WHERE id_docente=?',[$datos['id_docente']])) $error = 'Selecciona un docente evaluador.';
    elseif (!in_array($datos['trimestre'],['1','2','3'],true)) $error = 'Selecciona un trimestre entre 1 y 3.';
    elseif (!is_numeric($datos['nota']) || (float)$datos['nota']<0 || (float)$datos['nota']>100) $error = 'La nota debe ser un número entre 0 y 100.';
    elseif (longitud($datos['observacion'])>255) $error = 'La observación no debe superar 255 caracteres.';
    elseif (valor('SELECT COUNT(*) FROM kardex WHERE re_estudiante=? AND id_materia=? AND trimestre=? AND id_kardex<>?',[$datos['re_estudiante'],$datos['id_materia'],$datos['trimestre'],$editar ? $id : 0])) $error = 'Ya existe una nota de esta materia y trimestre para el estudiante. Puedes editarla desde Calificaciones.';
    if (!$error && !exigir_mfa($error)) { /* MFA requerido para modificar calificaciones. */ }
    if (!$error) try {
        $pdo->beginTransaction();
        $args = [$datos['re_estudiante'],$datos['id_materia'],$datos['id_docente'],$datos['trimestre'],round((float)$datos['nota'],2),$datos['observacion'] ?: 'Sin observaciones'];
        if ($editar) { $args[]=$id; consulta('UPDATE kardex SET re_estudiante=?,id_materia=?,id_docente=?,trimestre=?,nota=?,observacion=? WHERE id_kardex=?',$args); $kid=(int)$id; }
        else { consulta('INSERT INTO kardex (re_estudiante,id_materia,id_docente,trimestre,nota,observacion) VALUES (?,?,?,?,?,?)',$args); $kid=(int)$pdo->lastInsertId(); }
        guardar_traza_nota($kid, $editar ? 'editar' : 'crear', $antes);
        $pdo->commit();
        aviso($editar ? 'Calificación actualizada.' : 'Calificación registrada correctamente.'); ir('ver_kardex.php?re='.urlencode($datos['re_estudiante']));
    } catch(PDOException $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); $error=errorBD($ex); }
      catch(Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log('guardar_nota: '.$ex->getMessage()); $error='No se pudo guardar la calificación de forma segura. No se confirmó el cambio.'; }
}
cabecera($editar ? 'Editar calificación' : 'Registrar calificación','notas','Cada resultado es parte del recorrido académico del estudiante.');
?>
<a class="back-link" href="notas.php">← Volver a calificaciones</a>
<div class="form-layout">
<section class="panel">
<div class="panel-head">
<h2>Datos de la evaluación</h2>
</div>
<div class="panel-body">
<?php errorFormulario($error); ?>
<?php if(!$estudiantes || !$materias || !$docentes): ?>
<div class="alert info">Para registrar notas necesitas estudiantes, materias y docentes. <?= rol()==='admin' ? 'Completa esos registros desde el menú lateral.' : 'Solicita al administrador que complete los registros y te asigne materias.' ?>
</div>
<?php else: ?>
<form method="post">
<?= csrf_campo() ?>
<div class="form-grid">
<div class="field full">
<label for="re_estudiante">Estudiante</label>
<select id="re_estudiante" name="re_estudiante" required>
<option value="">Seleccionar estudiante</option>
<?php foreach($estudiantes as $f): ?>
<option value="<?= e($f['re']) ?>"<?= seleccionado($datos['re_estudiante'],$f['re']) ?>>
<?= e($f['apellidos'].' '.$f['nombres'].' · '.$f['curso'].' · '.$f['re']) ?>
</option>
<?php endforeach ?>
</select>
</div>
<div class="field">
<label for="id_materia">Materia</label>
<select id="id_materia" name="id_materia" required data-materia-docente>
<option value="">Seleccionar materia</option>
<?php foreach($materias as $m): ?>
<option data-docente="<?= e($m['id_docente']) ?>" value="<?= $m['id_materia'] ?>"<?= seleccionado($datos['id_materia'],$m['id_materia']) ?>>
<?= e($m['nombre_materia']) ?>
</option>
<?php endforeach ?>
</select>
</div>
<div class="field">
<label for="id_docente">Docente evaluador</label>
<select id="id_docente" name="id_docente" required>
<option value="">Seleccionar docente</option>
<?php foreach($docentes as $d): ?>
<option value="<?= $d['id_docente'] ?>"<?= seleccionado($datos['id_docente'],$d['id_docente']) ?>>
<?= e(nombre($d)) ?>
</option>
<?php endforeach ?>
</select>
</div>
<div class="field">
<label for="trimestre">Trimestre</label>
<select id="trimestre" name="trimestre" required>
<?php foreach([1,2,3] as $t): ?>
<option value="<?= $t ?>"<?= seleccionado($datos['trimestre'],$t) ?>>
<?= $t ?>º trimestre</option>
<?php endforeach ?>
</select>
</div>
<?php campo('nota','Nota sobre 100',$datos['nota'],'number','required min="0" max="100" step="0.01" placeholder="Ej. 85.50"'); ?>
<div class="field full">
<label for="observacion">Observación (opcional)</label>
<textarea id="observacion" name="observacion" maxlength="255" rows="3" placeholder="Un comentario sobre el desempeño del estudiante">
<?= e($datos['observacion'] === 'Sin observaciones' ? '' : $datos['observacion']) ?>
</textarea>
</div>
</div>
<?= mfa_campo() ?>
<div class="form-actions">
<a class="button secondary" href="notas.php">Cancelar</a>
<button class="button" type="submit">Guardar calificación</button>
</div>
</form>
<?php endif ?>
</div>
</section>
<aside class="note-panel">
<h3>Una nota por trimestre</h3>
<p>Registra la calificación consolidada de la materia para el trimestre elegido. Si necesitas corregirla, utiliza Editar.</p>
<p>Se aceptan notas de 0 a 100, con hasta dos decimales. El promedio final se muestra cuando los tres trimestres están registrados.</p>
</aside>
</div>
<?php pie(); ?>
