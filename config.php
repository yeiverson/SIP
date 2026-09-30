<?php
/**
 * Configuración principal de la aplicación.
 * Este archivo debe requerirse desde la raíz del proyecto.
 */

if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '/');

    $indexPos = strpos($script, '/index.php');
    if ($indexPos !== false) {
        $basePath = substr($script, 0, $indexPos);
    } else {
        $basePath = rtrim(dirname($script), '/\\');
    }

    define('BASE_URL', $scheme . '://' . $host . ($basePath === '/' ? '' : $basePath) . '/');
}

if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'America/Caracas');
}

date_default_timezone_set(APP_TIMEZONE);

require_once __DIR__ . '/config/database.php';
