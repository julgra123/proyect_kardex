<?php
// Configuración compatible con XAMPP y Aiven en la nube.
date_default_timezone_set('America/La_Paz');
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (PHP_SAPI !== 'cli') {
    // Sesión endurecida (Política 2)
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('KARDEXSESS');
        session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$https,'httponly'=>true,'samesite'=>'Strict']);
        session_start();
    }
    // Cabeceras de seguridad
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
    header('Cache-Control: no-store');
    if ($https) header('Strict-Transport-Security: max-age=31536000');
    header_remove('X-Powered-By');
} elseif (!isset($_SESSION)) { $_SESSION = []; }

// Credenciales: config_local.php, variables de entorno o valores por defecto de XAMPP.
$cfg = is_file(__DIR__ . '/includes/config_local.php') ? (require __DIR__ . '/includes/config_local.php') : [];
$host = $cfg['host'] ?? (getenv('KARDEX_DB_HOST') ?: 'localhost');
$port = $cfg['port'] ?? (getenv('KARDEX_DB_PORT') ?: '3306'); // <-- NUEVO: Soporte para el puerto de Aiven
$db   = $cfg['db']   ?? (getenv('KARDEX_DB_NAME') ?: 'kardex_prueba');
$user = $cfg['user'] ?? (getenv('KARDEX_DB_USER') ?: 'root');
$pass = $cfg['pass'] ?? (getenv('KARDEX_DB_PASS') !== false ? getenv('KARDEX_DB_PASS') : '');

try {
    // Opciones base de PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // <-- NUEVO: Si existe el archivo ca.pem de Aiven, se activa el cifrado SSL obligatorio
    // Cambia esta parte en tu conexion.php:
$ssl_ca = file_exists('/etc/secrets/ca.pem') ? '/etc/secrets/ca.pem' : __DIR__ . '/includes/ca.pem';

if (is_file($ssl_ca)) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
}
    // Se incluye el parámetro ;port=$port en el DSN
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, $options);

} catch (PDOException $e) {
    http_response_code(503);
    exit('<h1>No se pudo conectar al kardex</h1>
<p>Revisa que la base de datos esté accesible, que las credenciales sean correctas y que el certificado SSL (ca.pem) esté ubicado en la carpeta includes.</p>');
}
require_once __DIR__ . '/includes/seguridad.php';
