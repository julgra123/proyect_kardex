<?php
// Uso (desde XAMPP Shell, dentro de la carpeta kardex):
//   C:\xampp\php\php.exe tools\diagnostico_totp.php CLAVE_QUE_MUESTRA_LA_PANTALLA
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../includes/totp.php';
$s = $argv[1] ?? '';
if ($s === '') { echo "Falta la clave. Ejemplo: php tools\\diagnostico_totp.php ABCD EFGH ...\n"; exit(1); }
$s = implode('', array_slice($argv, 1));            // acepta la clave con espacios
$s = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $s));
echo "Clave leida: $s (" . strlen($s) . " caracteres, debe ser 32)\n";
echo "Hora UTC del servidor : " . gmdate('Y-m-d H:i:s') . "\n";
echo "Hora local del servidor: " . date('Y-m-d H:i:s T') . "\n";
$p = intdiv(time(), 30);
foreach ([-2 => 'hace 60 s', -1 => 'hace 30 s', 0 => 'AHORA', 1 => 'en 30 s', 2 => 'en 60 s'] as $d => $t)
    printf("  %-10s %s\n", $t, totp_codigo($s, $p + $d));
echo "\nCompara con tu app: si coincide con 'hace/en 30-60 s', el reloj esta desfasado.\n";
echo "Si no coincide con ninguno, la clave de la app es distinta a la de la pantalla.\n";
