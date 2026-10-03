<?php
/**
 * Configuración principal de la aplicación.
 * Este archivo debe requerirse desde la raíz del proyecto.
 */

if (!defined('BASE_URL')) {
    $envUrl = getenv('BASE_URL');
    if ($envUrl && $envUrl !== false) {
        define('BASE_URL', rtrim($envUrl, '/') . '/');
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__) ?: __DIR__), '/');
        $docRoot     = !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']), '/') : '';
        $scriptName  = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));
        $scriptFile  = !empty($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']) : '';

        $basePath = '';
        if ($docRoot && stripos($projectRoot, $docRoot) === 0) {
            $basePath = substr($projectRoot, strlen($docRoot));
        } elseif ($scriptFile && $projectRoot && stripos($scriptFile, $projectRoot) === 0) {
            $relScript = substr($scriptFile, strlen($projectRoot));
            if ($relScript !== '' && (substr($scriptName, -strlen($relScript)) === $relScript)) {
                $basePath = substr($scriptName, 0, -strlen($relScript));
            }
        } else {
            $folderName = '/' . basename($projectRoot);
            $pos = stripos($scriptName, $folderName);
            if ($pos !== false) {
                $basePath = substr($scriptName, 0, $pos + strlen($folderName));
            }
        }

        $basePath = '/' . trim($basePath, '/');
        if ($basePath === '/') {
            $basePath = '';
        }

        define('BASE_URL', $scheme . '://' . $host . $basePath . '/');
    }
}

if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'America/Caracas');
}

date_default_timezone_set(APP_TIMEZONE);

// Autoloader PSR-4 nativo — carga automática de modelos e includes
require_once __DIR__ . '/includes/autoloader.php';

// Logger de archivo — disponible en toda la aplicación
require_once __DIR__ . '/includes/Logger.php';

require_once __DIR__ . '/config/database.php';

