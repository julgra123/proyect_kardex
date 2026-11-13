<?php
require_once 'includes/layout.php'; verificarSesion(); autorizar('auditoria.ver');
$roles = ['admin','auditor','coordinador','docente','estudiante'];
$ops = array_column(todos('SELECT DISTINCT operacion FROM permisos ORDER BY operacion'), 'operacion');
$tiene = []; foreach (todos('SELECT rol,operacion FROM permisos') as $r) $tiene[$r['rol'].'|'.$r['operacion']] = true;
$ctx = ['nota.crear'=>'docente: materia y curso asignados', 'nota.editar'=>'docente: materia y curso asignados', 'nota.eliminar'=>'docente: materia y curso asignados', 'kardex.ver'=>'estudiante: propio; docente: cursos de sus materias', 'identidad.gestionar'=>'nunca sobre la propia cuenta + 2FA', 'identidad.cambiar_rol'=>'nunca sobre la propia cuenta + 2FA'];
cabecera('Matriz de permisos', 'matriz', 'Mínimo privilegio: rol × operación. El servidor aplica esta matriz en cada petición.');
?>
<section class="panel"><div class="table-wrap"><table class="matrix">
<thead><tr><th>Operación</th><?php foreach($roles as $r): ?><th><?= e($r) ?></th><?php endforeach ?><th>Regla de contexto</th></tr></thead><tbody>
<?php foreach($ops as $o): ?><tr><td class="mono"><?= e($o) ?></td>
<?php foreach($roles as $r): ?><td><?= isset($tiene[$r.'|'.$o]) ? '●' : '<span class="muted">—</span>' ?></td><?php endforeach ?>
<td class="observation"><?= e($ctx[$o] ?? '') ?></td></tr><?php endforeach ?>
</tbody></table></div></section>
<?php pie(); ?>
