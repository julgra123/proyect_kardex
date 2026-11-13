<?php
require_once 'includes/layout.php'; verificarSesion();
$tipo = $_GET['tipo'] ?? ''; $id = $_GET['id'] ?? '';
$mapa = ['estudiante'=>['estudiantes','re','estudiantes.php','estudiante.baja'], 'docente'=>['docentes','id_docente','profesores.php','docente.baja'], 'materia'=>['materias','id_materia','materias.php','materia.eliminar'], 'nota'=>['kardex','id_kardex','notas.php','nota.eliminar']];
if (!isset($mapa[$tipo])) noExiste();
[$tabla, $clave, $volver, $op] = $mapa[$tipo];
$dato = uno("SELECT * FROM $tabla WHERE $clave=?", [$id]); if (!$dato) noExiste();
if ($tipo === 'nota') autorizar($op, ['id_materia'=>$dato['id_materia'], 're_estudiante'=>$dato['re_estudiante']]); else autorizar($op);
// Estudiantes y docentes NO se borran: se da de baja su identidad (revocada) y se conservan sus registros y notas.
$logico = in_array($tipo, ['estudiante', 'docente'], true);
$ident = null;
if ($tipo === 'estudiante') $ident = uno("SELECT * FROM identidades WHERE rol='estudiante' AND ref_id=?", [$id]);
if ($tipo === 'docente') $ident = uno("SELECT * FROM identidades WHERE rol IN ('docente','coordinador') AND ref_id=?", [$id]);
$etiqueta = $tipo === 'materia' ? $dato['nombre_materia'] : ($tipo === 'nota' ? 'Nota de '.$dato['nota'].' · trimestre '.$dato['trimestre'] : nombre($dato));
$notas = 0; $materias = 0;
if ($tipo === 'estudiante') $notas = valor('SELECT COUNT(*) FROM kardex WHERE re_estudiante=?', [$id]);
if ($tipo === 'materia') $notas = valor('SELECT COUNT(*) FROM kardex WHERE id_materia=?', [$id]);
if ($tipo === 'docente') { $notas = valor('SELECT COUNT(*) FROM kardex WHERE id_docente=?', [$id]); $materias = valor('SELECT COUNT(*) FROM materias WHERE id_docente=?', [$id]); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('confirmar') === 'si') {
    $motivo = post('motivo');
    if ($logico && longitud($motivo) < 5) $error = 'Indica el motivo de la baja (mínimo 5 caracteres).';
    elseif ($tipo === 'materia' && $notas) $error = 'No se puede eliminar una materia con calificaciones registradas. Reasígnala o conserva su historial.';
    elseif ($logico && (!$ident || $ident['estado'] === 'revocada')) $error = 'Esta cuenta ya está dada de baja o no tiene identidad asociada.';
    elseif (!exigir_mfa($error)) { }
    else try {
        if ($logico) {
            cambiar_estado_identidad($ident, 'revocada', $motivo);
            aviso('Baja registrada: la cuenta ya no puede ingresar y sus calificaciones se conservaron.');
        } else {
            $pdo->beginTransaction();
            try {
                if ($tipo === 'nota') {
                    $antes = $dato;
                    registrar_historial((int)$id, 'eliminar', $antes, null);
                    consulta("DELETE FROM $tabla WHERE $clave=?", [$id]);
                    auditar('nota_eliminada', 'ok', 'kardex#'.$id, nota_snap($antes), 'alerta', null, null, null, true);
                } else {
                    consulta("DELETE FROM $tabla WHERE $clave=?", [$id]);
                    auditar('materia_eliminada', 'ok', 'materia#'.$id, ['nombre'=>$dato['nombre_materia']], 'alerta', null, null, null, true);
                }
                $pdo->commit();
                aviso('Registro eliminado correctamente.');
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $ex;
            }
        }
        ir($volver);
    } catch (PDOException $ex) { $error = errorBD($ex); }
      catch (Throwable $ex) { $error = 'No se pudo completar la operación de forma segura. No se confirmó el cambio.'; }
}
cabecera($logico ? 'Dar de baja' : 'Eliminar registro', $tipo === 'docente' ? 'docentes' : ($tipo === 'materia' ? 'materias' : ($tipo === 'nota' ? 'notas' : 'estudiantes')), 'Revisa la información antes de continuar.');
?>
<section class="panel">
<div class="panel-body">
<?php errorFormulario($error); ?>
<h2><?= e($etiqueta) ?></h2>
<?php if ($logico): ?>
<p>La cuenta pasará a estado <strong>revocada</strong> (definitivo): perderá el acceso de inmediato y se cerrarán sus sesiones. Los registros y calificaciones <strong>se conservan</strong>.</p>
<?php if ($notas): ?><div class="alert info">Se conservarán <?= (int)$notas ?> calificaciones asociadas.</div><?php endif ?>
<?php if ($materias): ?><p><?= (int)$materias ?> materias quedarán sin docente asignado para reasignarlas.</p><?php endif ?>
<?php else: ?>
<p>Vas a eliminar este registro de forma permanente<?= $tipo === 'nota' ? ' (se guarda una copia en el historial y se puede restaurar desde Integridad)' : '' ?>.</p>
<?php if ($tipo === 'materia' && $notas): ?><div class="alert error">Esta materia tiene <?= (int)$notas ?> calificaciones, por eso no se puede eliminar.</div><?php endif ?>
<?php endif ?>
<form method="post">
<?= csrf_campo() ?>
<input type="hidden" name="confirmar" value="si">
<?php if ($logico): ?><div class="field"><label for="motivo">Motivo de la baja</label><input id="motivo" name="motivo" required minlength="5" maxlength="255"></div><?php endif ?>
<?= mfa_campo() ?>
<div class="form-actions">
<a class="button secondary" href="<?= e($volver) ?>">Cancelar</a>
<button class="button danger" type="submit"><?= $logico ? 'Confirmar baja' : 'Confirmar eliminación' ?></button>
</div>
</form>
</div>
</section>
<?php pie(); ?>
