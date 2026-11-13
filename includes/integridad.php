<?php
// Integridad del kardex (Parte 5): sello HMAC por registro + historial de cambios + restauración.
function nota_snap($f) {
    return ['id_kardex'=>(int)$f['id_kardex'], 're_estudiante'=>$f['re_estudiante'], 'id_materia'=>(int)$f['id_materia'], 'id_docente'=>(int)$f['id_docente'],
            'trimestre'=>(int)$f['trimestre'], 'nota'=>number_format((float)$f['nota'], 2, '.', ''), 'observacion'=>(string)$f['observacion']];
}
function sello_nota($f) { return hash_hmac('sha256', implode('|', nota_snap($f)), clave_app()); }
function sellar_nota($id) {
    $f = uno('SELECT * FROM kardex WHERE id_kardex=?', [$id]);
    if ($f) consulta('UPDATE kardex SET sello=? WHERE id_kardex=?', [sello_nota($f), $id]);
    return $f;
}
function registrar_historial($id, $accion, $antes, $despues) {
    consulta('INSERT INTO kardex_historial (id_kardex,accion,datos_antes,datos_despues,id_identidad,momento) VALUES (?,?,?,?,?,?)',
        [$id, $accion, $antes ? json_encode(nota_snap($antes)) : null, $despues ? json_encode(nota_snap($despues)) : null, $_SESSION['id_identidad'] ?? null, ahora()]);
}
// Llamar después de INSERT/UPDATE en kardex.
function guardar_traza_nota($id, $accion, $antes) {
    $despues = sellar_nota($id);
    registrar_historial($id, $accion, $antes, $despues);
    auditar($accion === 'crear' ? 'nota_creada' : 'nota_editada', 'ok', 'kardex#' . $id,
        ['estudiante'=>$despues['re_estudiante'], 'materia'=>$despues['id_materia'], 'trimestre'=>$despues['trimestre'], 'nota_antes'=>$antes['nota'] ?? null, 'nota_despues'=>$despues['nota']], 'info', null, null, null, true);
}
function restaurar_nota($id) {
    global $pdo;
    $h = uno('SELECT * FROM kardex_historial WHERE id_kardex=? AND accion<>\'migracion\' ORDER BY id DESC LIMIT 1', [$id]);
    if (!$h) $h = uno('SELECT * FROM kardex_historial WHERE id_kardex=? ORDER BY id DESC LIMIT 1', [$id]);
    if (!$h) return 'No existe historial para esta calificación.';
    $d = json_decode($h['accion'] === 'eliminar' ? $h['datos_antes'] : $h['datos_despues'], true);
    if (!$d) return 'El historial de esta calificación no es legible.';
    $own = !$pdo->inTransaction();
    if ($own) $pdo->beginTransaction();
    try {
        $actual = uno('SELECT * FROM kardex WHERE id_kardex=? FOR UPDATE', [$id]);
        if ($actual) consulta('UPDATE kardex SET re_estudiante=?,id_materia=?,id_docente=?,trimestre=?,nota=?,observacion=? WHERE id_kardex=?',
            [$d['re_estudiante'], $d['id_materia'], $d['id_docente'], $d['trimestre'], $d['nota'], $d['observacion'], $id]);
        else consulta('INSERT INTO kardex (id_kardex,re_estudiante,id_materia,id_docente,trimestre,nota,observacion) VALUES (?,?,?,?,?,?,?)',
            [$id, $d['re_estudiante'], $d['id_materia'], $d['id_docente'], $d['trimestre'], $d['nota'], $d['observacion']]);
        $nuevo = sellar_nota($id);
        registrar_historial($id, 'restaurar', $actual ?: null, $nuevo);
        auditar('nota_restaurada', 'ok', 'kardex#' . $id, ['desde_historial'=>$h['id']], 'critica', null, null, null, true);
        if ($own) $pdo->commit();
        return '';
    } catch (Throwable $ex) {
        if ($own && $pdo->inTransaction()) $pdo->rollBack();
        error_log('restaurar_nota: ' . $ex->getMessage());
        return 'No se pudo restaurar la calificación; no se confirmó el cambio ni su auditoría.';
    }
}
// Verifica la cadena de hashes de la auditoría. Devuelve [total, id_primer_fallo|null].
function verificar_cadena_auditoria() {
    global $pdo; $prev = str_repeat('0', 64); $n = 0;
    foreach ($pdo->query('SELECT * FROM auditoria ORDER BY id') as $r) {
        $n++;
        $h = hash('sha256', implode('|', [$prev, $r['momento'], $r['id_identidad'], $r['usuario'], $r['rol'], $r['evento'], $r['resultado'], $r['recurso'], $r['detalle'], $r['ip'], $r['severidad']]));
        if ($r['hash_prev'] !== $prev || $r['hash'] !== $h) return [$n, (int)$r['id']];
        $prev = $r['hash'];
    }
    return [$n, null];
}
