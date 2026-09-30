<?php
/**
 * Configuración unificada de base de datos.
 * Lee credenciales desde variables de entorno y usa UTF-8 en PostgreSQL.
 */

$db_host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : 'localhost');
$db_port = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : '5432');
$db_name = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : 'unefa_postgrados');
$db_user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'postgres');
$db_password = getenv('DB_PASSWORD') ?: (defined('DB_PASSWORD') ? DB_PASSWORD : 'postgres');

$pdo = null;

try {
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $db_host, $db_port, $db_name);
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
    ]);

    // Asegurar codificación UTF-8 para PostgreSQL
    try {
        $pdo->exec("SET NAMES 'UTF8'");
        $pdo->exec("SET client_encoding TO 'UTF8'");
    } catch (Throwable $e) {
        error_log('[SIP] Warning: no se pudo forzar client_encoding UTF8: ' . $e->getMessage());
    }
} catch (PDOException $e) {
    error_log('[SIP] Error de conexión BD: ' . $e->getMessage());

    if (php_sapi_name() !== 'cli') {
        http_response_code(503);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sistema temporalmente no disponible</title></head>';
        echo '<body style="font-family:Arial,sans-serif;background:#f8f9fa;color:#333;padding:40px;">';
        echo '<div style="max-width:560px;margin:80px auto;background:#fff;border:1px solid #eaeaea;border-radius:12px;padding:24px;box-shadow:0 8px 20px rgba(0,0,0,.06);">';
        echo '<h2 style="margin:0 0 12px;color:#0B2545;">Sistema temporalmente no disponible</h2>';
        echo '<p style="margin:0;line-height:1.6;">La base de datos del sistema no está disponible en este momento. Inténtelo nuevamente más tarde.</p>';
        echo '</div></body></html>';
    }
    exit;
}
