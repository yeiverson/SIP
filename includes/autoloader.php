<?php
/**
 * Autoloader PSR-4 nativo — SIP-Postgrado UNEFA
 *
 * Mapea clases a archivos dentro de los directorios registrados.
 * Se carga una única vez desde config.php.
 *
 * Convención: clase "Usuario" → "modelos/Usuario.php"
 *             clase "Logger"  → "includes/Logger.php"
 */

spl_autoload_register(function (string $class): void {
    // Directorios donde buscar clases (orden de prioridad)
    $dirs = [
        __DIR__ . '/../modelos/',
        __DIR__ . '/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
