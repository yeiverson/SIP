<?php
/**
 * Configuración unificada de base de datos con lectura desde variables de entorno
 * y manejo de codificación UTF-8.
 */

$db_host     = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : 'localhost');
$db_port     = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : '5432');
$db_name     = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : 'unefa_postgrados');
$db_user     = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'postgres');
$db_password = getenv('DB_PASSWORD') ?: (defined('DB_PASSWORD') ? DB_PASSWORD : 'postgres');

try {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Asegurar que la conexión use UTF-8 (cliente)
    try {
        $pdo->exec("SET NAMES 'UTF8'");
        $pdo->exec("SET client_encoding TO 'UTF8'");
    } catch (Throwable $e) {
        // No crítico: solo logueamos
        error_log("[SIP] Warning: no se pudo forzar client_encoding UTF8: " . $e->getMessage());
    }

} catch (PDOException $e) {
    error_log("[SIP] Error de conexión BD: " . $e->getMessage());

    // Mensaje amigable para producción, sin exponer detalles sensibles.
    if (php_sapi_name() !== 'cli') {
        http_response_code(503);
        echo "<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>Sistema temporalmente no disponible</title></head><body style=\"font-family:Arial,Helvetica,sans-serif;background:#F8F9FA;color:#333;padding:30px;\"><h1>Servicio no disponible</h1><p>La aplicación no puede conectarse a la base de datos en este momento. Si eres el administrador, revisa la configuración de conexión (host, puerto, usuario, contraseña).</p></body></html>";
    }
    exit;
}
