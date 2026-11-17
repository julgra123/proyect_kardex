<?php
require_once 'includes/layout.php';
verificarSesion();

/*
|--------------------------------------------------------------------------
| 1. CONTROL DE ACCESO AL KARDEX
|--------------------------------------------------------------------------
*/

if (rol() === 'estudiante') {

    // El estudiante solo puede consultar su propio kardex.
    $re = (string) $_SESSION['usuario_id'];

    // Detectar si intenta modificar el RE desde la URL.
    $reSolicitado = (string) ($_GET['re'] ?? $re);

    if ($reSolicitado !== $re) {

        // Registrar el intento de acceso no autorizado.
        auditar(
            'acceso_denegado',
            'denegado',
            'kardex.ver',
            [
                'razon' => 'kardex_ajeno',
                're_solicitado' => $reSolicitado
            ],
            'alerta'
        );

        http_response_code(403);

        exit('Acceso denegado: no puedes consultar el kardex de otro estudiante.');
    }

} else {

    // Los demás roles utilizan el RE indicado en la URL.
    $re = (string) ($_GET['re'] ?? '');
}

// Verificar los permisos correspondientes.
autorizar('kardex.ver', ['re' => $re]);

/*
|--------------------------------------------------------------------------
| 2. OBTENER INFORMACIÓN DEL ESTUDIANTE
|--------------------------------------------------------------------------
*/

$estudiante = uno(
    'SELECT * FROM estudiantes WHERE re=?',
    [$re]
);

if (!$estudiante) {
    noExiste('Estudiante');
}

/*
|--------------------------------------------------------------------------
| 3. OBTENER CALIFICACIONES
|--------------------------------------------------------------------------
*/


$notas = todos(
    'SELECT
        k.*,
        m.nombre_materia,
        CONCAT(d.nombres, \' \', d.apellidos) AS docente
    FROM kardex k
    JOIN materias m ON m.id_materia=k.id_materia
    JOIN docentes d ON d.id_docente=k.id_docente
    JOIN estudiantes e ON e.re=k.re_estudiante
    WHERE k.re_estudiante=?'
    . (
        rol() === 'docente'
        ? ' AND m.id_docente=?
           AND EXISTS (
               SELECT 1
               FROM materia_cursos mc
               WHERE mc.id_materia=k.id_materia
               AND mc.curso=e.curso
           )'
        : ''
    )
    . ' ORDER BY m.nombre_materia,k.trimestre,k.id_kardex',
    rol() === 'docente'
        ? [$re, $_SESSION['usuario_id']]
        : [$re]
);


/*
|--------------------------------------------------------------------------
| 4. CALCULAR RESUMEN POR MATERIA
|--------------------------------------------------------------------------
*/

$resumen = [];
$duplicadas = false;

foreach ($notas as $n) {

    $mid = $n['id_materia'];

    $resumen[$mid]['nombre'] = $n['nombre_materia'];

    $resumen[$mid]['trimestres'][$n['trimestre']][] =
        (float) $n['nota'];
}

foreach ($resumen as &$r) {

    $r['valores'] = [];

    foreach ($r['trimestres'] as $t => $valores) {

        if (count($valores) > 1) {
            $duplicadas = true;
        }

        $r['valores'][$t] =
            array_sum($valores) / count($valores);
    }

    $r['promedio'] = round(
        array_sum($r['valores']) / count($r['valores']),
        2
    );

    $r['completo'] = count($r['valores']) === 3;
}

unset($r);

/*
|--------------------------------------------------------------------------
| 5. ACCIONES Y ENCABEZADO
|--------------------------------------------------------------------------
*/

$acciones = '<button class="button secondary" type="button" data-print>Imprimir kardex</button>';

if (tiene_permiso('nota.crear')) {

    $acciones .=
        '<a class="button" href="registrar_nota.php?re='
        . urlencode($re)
        . '">'
        . icono('plus')
        . ' Registrar nota</a>';
}

cabecera(
    isset($miKardex) ? 'Mi kardex' : 'Kardex académico',
    rol() === 'estudiante' ? 'kardex' : 'estudiantes',
    'El recorrido académico del estudiante, materia por materia.',
    $acciones
);
?>

<!-- RESUMEN DEL ESTUDIANTE -->

<section class="panel student-summary">

    <span class="avatar">
        <?= e(inicial($estudiante['nombres'])) ?>
    </span>

    <div>

        <p class="eyebrow">FICHA ESTUDIANTIL</p>

        <h2>
            <?= e(nombre($estudiante)) ?>
        </h2>

        <div class="summary-meta">

            <span>
                R.E. <?= e($estudiante['re']) ?>
            </span>

            <span>
                <?= e($estudiante['curso']) ?>
            </span>

            <span>
                Nacimiento:
                <?= e(date('d/m/Y', strtotime($estudiante['fecha_nacimiento']))) ?>
            </span>

        </div>

    </div>

</section>

<?php if (rol() === 'docente'): ?>

    <div class="alert info">
        Estás viendo solo las calificaciones de las materias que tienes asignadas.
    </div>

<?php endif ?>

<?php if ($duplicadas): ?>

    <div class="alert info">
        Existen notas antiguas repetidas para una misma materia y trimestre.
        En el resumen se muestra su promedio.
        Revisa esos registros en el detalle antes de considerar definitivo el resultado.
    </div>

<?php endif ?>

<!-- RESUMEN DE CALIFICACIONES -->

<section class="panel">

    <div class="panel-head">

        <div>
            <h2>Resumen por materia</h2>

            <p>
                Promedio simple de los trimestres registrados.
                La nota final requiere los tres trimestres.
            </p>
        </div>

    </div>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>Materia</th>
                    <th>1º trim.</th>
                    <th>2º trim.</th>
                    <th>3º trim.</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($resumen as $r): ?>

                    <tr>

                        <td>
                            <strong>
                                <?= e($r['nombre']) ?>
                            </strong>
                        </td>

                        <?php foreach ([1, 2, 3] as $t): ?>

                            <td>
                                <?= isset($r['valores'][$t])
                                    ? number_format($r['valores'][$t], 2)
                                    : '<span class="muted">Pendiente</span>' ?>
                            </td>

                        <?php endforeach ?>

                        <td>

                            <strong class="grade <?= $r['promedio'] >= 51 ? 'good' : 'bad' ?>">
                                <?= number_format($r['promedio'], 2) ?>
                            </strong>

                            <small class="muted">
                                <?= $r['completo'] ? 'final' : 'parcial' ?>
                            </small>

                        </td>

                        <td>

                            <?php if (!$r['completo']): ?>

                                <span class="badge neutral">
                                    En curso
                                </span>

                            <?php else: ?>

                                <span class="badge <?= $r['promedio'] >= 51 ? '' : 'bad' ?>">
                                    <?= $r['promedio'] >= 51 ? 'Aprobado' : 'Reprobado' ?>
                                </span>

                            <?php endif ?>

                        </td>

                    </tr>

                <?php endforeach ?>

                <?php if (!$resumen): ?>

                    <?php vacio(6, 'Las calificaciones aparecerán cuando sean registradas.'); ?>

                <?php endif ?>

            </tbody>

        </table>

    </div>

    <div class="panel-body">

        <small class="muted">
            Criterio de esta versión: aprobación desde 51/100.
            Los trimestres pendientes no se cuentan como cero.
            Materias sin notas todavía no aparecen en este resumen.
        </small>

    </div>

</section>

<!-- DETALLE DE EVALUACIONES -->

<section class="panel">

    <div class="panel-head">

        <h2>Detalle de evaluaciones</h2>

        <span class="badge neutral">
            <?= count($notas) ?> registros
        </span>

    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>Materia</th>
                    <th>Trimestre</th>
                    <th>Nota</th>
                    <th>Docente / Observación</th>

                    <?php if (tiene_permiso('nota.editar')): ?>
                        <th class="no-print">Acciones</th>
                    <?php endif ?>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($notas as $n): ?>

                    <tr>

                        <td>
                            <?= e($n['nombre_materia']) ?>
                        </td>

                        <td>
                            <?= $n['trimestre'] ?>º trimestre
                        </td>

                        <td>
                            <strong>
                                <?= number_format($n['nota'], 2) ?>
                            </strong>
                        </td>

                        <td class="observation">

                            <?= e($n['docente']) ?>

                            <br>

                            <small class="muted">
                                <?= e($n['observacion']) ?>
                            </small>

                        </td>

                        <?php if (tiene_permiso('nota.editar')): ?>

                            <td class="no-print">

                                <div class="actions">

                                    <a href="editar_nota.php?id=<?= $n['id_kardex'] ?>">
                                        Editar
                                    </a>

                                    <a
                                        class="danger-link"
                                        href="eliminar.php?tipo=nota&amp;id=<?= $n['id_kardex'] ?>"
                                    >
                                        Eliminar
                                    </a>

                                </div>

                            </td>

                        <?php endif ?>

                    </tr>

                <?php endforeach ?>

                <?php if (!$notas): ?>

                    <?php vacio(
                        tiene_permiso('nota.editar') ? 5 : 4,
                        'No hay evaluaciones registradas.'
                    ); ?>

                <?php endif ?>

            </tbody>

        </table>

    </div>

</section>

<p class="print-only">
    Fecha de impresión: <?= date('d/m/Y') ?>
</p>

<?php pie(); ?>
