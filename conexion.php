
<?php
date_default_timezone_set('America/La_Paz');

$https = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
);
// ======================================
// 1. CONFIGURACIÓN DE SESIONES Y SEGURIDAD
// ======================================

if (PHP_SAPI !== 'cli') {

    if (session_status() === PHP_SESSION_NONE) {

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_name('KARDEXSESS');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        session_start();
    }

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');

    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

    header('Cache-Control: no-store');

    if ($https) {
        header('Strict-Transport-Security: max-age=31536000');
    }

    header_remove('X-Powered-By');

} elseif (!isset($_SESSION)) {
    $_SESSION = [];
}

// ======================================
// 2. CARGAR CONFIGURACIÓN LOCAL
// ======================================

$configFile = __DIR__ . '/includes/config_local.php';

$cfg = [];

if (is_file($configFile)) {
    $cfg = require $configFile;
    if (!is_array($cfg)) {
        $cfg = [];
    }
}

// ======================================
// 3. CREDENCIALES
// Render tiene prioridad sobre config_local.php
// ======================================

function obtenerConfiguracion($variable, $valorLocal = null) {

    $valor = getenv($variable);

    if ($valor !== false && $valor !== '') {
        return $valor;
    }

    return $valorLocal;
}

$host = obtenerConfiguracion(
    'KARDEX_DB_HOST',
    $cfg['host'] ?? 'localhost'
);

$port = obtenerConfiguracion(
    'KARDEX_DB_PORT',
    $cfg['port'] ?? '3306'
);

$db = obtenerConfiguracion(
    'KARDEX_DB_NAME',
    $cfg['db'] ?? 'kardex_prueba'
);

$user = obtenerConfiguracion(
    'KARDEX_DB_USER',
    $cfg['user'] ?? 'root'
);

$pass = obtenerConfiguracion(
    'KARDEX_DB_PASS',
    $cfg['pass'] ?? ''
);

// ======================================
// 4. CERTIFICADO SSL DE AIVEN
// ======================================

$ssl_ca = is_readable('/etc/secrets/ca.pem')
    ? '/etc/secrets/ca.pem'
    : __DIR__ . '/includes/ca.pem';

// ======================================
// 5. CONEXIÓN PDO
// ======================================

try {

    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException(
            'La extensión PDO MySQL no está habilitada.'
        );
    }

    if (!is_file($ssl_ca) || !is_readable($ssl_ca)) {
        throw new RuntimeException(
            'El certificado ca.pem no existe o PHP no puede leerlo.'
        );
    }

    $options = [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES => false,

        PDO::ATTR_TIMEOUT => 15,

        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,

        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
    ];

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        $options
    );

    // Comprobar que la base de datos responde.
    $pdo->query("SELECT 1");

} catch (Throwable $e) {

    error_log('Error de conexión MySQL: ' . $e->getMessage());

    http_response_code(503);

    exit(
        '<h1>No se pudo conectar a la base de datos</h1>
        <p>Verifica las credenciales, la disponibilidad del servidor y el certificado SSL.</p>'
    );
}

// ======================================
// 6. ARCHIVO DE SEGURIDAD
// ======================================

require_once __DIR__ . '/includes/seguridad.php';


