<?php
// Configuración compatible con XAMPP. Los valores se pueden sobreescribir con variables de entorno.
date_default_timezone_set('America/La_Paz');
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (PHP_SAPI !== 'cli') {
    // Sesión endurecida (Política 2): cookie HttpOnly, SameSite, modo estricto.
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('KARDEXSESS');
        session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$https,'httponly'=>true,'samesite'=>'Strict']);
        session_start();
    }
    // Cabeceras de seguridad (Política 6).
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
    header('Cache-Control: no-store');
    if ($https) header('Strict-Transport-Security: max-age=31536000');
    header_remove('X-Powered-By');
} elseif (!isset($_SESSION)) { $_SESSION = []; }

// Credenciales: primero includes/config_local.php (protegido por .htaccess y fuera del control de versiones),
// luego variables de entorno, y por último los valores de desarrollo de XAMPP.
$cfg = is_file(__DIR__ . '/includes/config_local.php') ? (require __DIR__ . '/includes/config_local.php') : [];
$host = $cfg['host'] ?? (getenv('KARDEX_DB_HOST') ?: 'localhost');
$db = $cfg['db'] ?? (getenv('KARDEX_DB_NAME') ?: 'kardex_prueba');
$user = $cfg['user'] ?? (getenv('KARDEX_DB_USER') ?: 'root');
$pass = $cfg['pass'] ?? (getenv('KARDEX_DB_PASS') !== false ? getenv('KARDEX_DB_PASS') : '');
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(503);
    exit('<h1>No se pudo conectar al kardex</h1>
<p>Revisa que MySQL esté iniciado, que hayas importado kardex_escolar.sql y migracion_seguridad.sql, y que los datos de conexion.php sean correctos.</p>');
}
require_once __DIR__ . '/includes/seguridad.php';