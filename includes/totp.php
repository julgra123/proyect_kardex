<?php
// TOTP (RFC 6238) sin librerías externas: segundo factor para operaciones de alto riesgo.
const B32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
function b32_encode($bin) {
    $bits = ''; foreach (str_split($bin) as $c) $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    $out = ''; foreach (str_split($bits, 5) as $ch) $out .= B32[bindec(str_pad($ch, 5, '0'))];
    return $out;
}
function b32_decode($s) {
    $s = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $s)); $bits = '';
    foreach (str_split($s) as $c) $bits .= str_pad(decbin(strpos(B32, $c)), 5, '0', STR_PAD_LEFT);
    $out = ''; foreach (str_split($bits, 8) as $b) if (strlen($b) === 8) $out .= chr(bindec($b));
    return $out;
}
function totp_secreto_nuevo() { return b32_encode(random_bytes(20)); }
function totp_codigo($secretoB32, $paso) {
    $h = hash_hmac('sha1', pack('N2', 0, $paso), b32_decode($secretoB32), true);
    $o = ord($h[19]) & 0x0f;
    $n = ((ord($h[$o]) & 0x7f) << 24) | (ord($h[$o+1]) << 16) | (ord($h[$o+2]) << 8) | ord($h[$o+3]);
    return str_pad((string)($n % 1000000), 6, '0', STR_PAD_LEFT);
}
// Devuelve el paso aceptado (para impedir reutilización del mismo código) o false.
function totp_verificar($secretoB32, $codigo, $ultimoPaso = 0) {
    if (!is_string($codigo) || !preg_match('/^\d{6}$/', $codigo) || !$secretoB32) return false;
    $ahora = intdiv(time(), 30);
    for ($d = -1; $d <= 1; $d++) { $p = $ahora + $d; if ($p > $ultimoPaso && hash_equals(totp_codigo($secretoB32, $p), $codigo)) return $p; }
    return false;
}
