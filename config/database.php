<?php
/**
 * Configuración unificada de base de datos con lectura desde variables de entorno (.env)
 * y manejo de codificación UTF-8.
 */

// Cargar archivo .env si existe y no está cargado previamente
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Quitar comillas si las tuviese
            $value = trim($value, '"\'');
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

$db_host     = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? 'localhost'));
$db_port     = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? '5432'));
$db_name     = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? 'unefa_postgrados'));
$db_user     = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? 'postgres'));
$db_password = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? ($_SERVER['DB_PASSWORD'] ?? ''));

try {
    $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name}";
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Asegurar que la conexión use UTF-8
    try {
        $pdo->exec("SET NAMES 'UTF8'");
        $pdo->exec("SET client_encoding TO 'UTF8'");
    } catch (Throwable $e) {
        error_log("[SIP] Warning: no se pudo forzar client_encoding UTF8: " . $e->getMessage());
    }

} catch (PDOException $e) {
    error_log("[SIP] Error de conexión BD: " . $e->getMessage());

    if (php_sapi_name() !== 'cli') {
        http_response_code(503);
        echo "<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>Sistema temporalmente no disponible</title></head><body style=\"font-family:Arial,Helvetica,sans-serif;background:#F8F9FA;color:#333;padding:30px;\"><h1>Servicio no disponible</h1><p>La aplicación no puede conectarse a la base de datos en este momento. Verifique la configuración en el archivo <code>.env</code>.</p></body></html>";
    }
    exit;
}
