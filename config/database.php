<?php
/**
 * Configuración unificada de base de datos.
 * Todas las conexiones deben require_once este archivo.
 */
$db_host     = "localhost";
$db_port     = "5432";
$db_name     = "unefa_postgrados";
$db_user     = "postgres";
$db_password = "yeiverson";

try {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("Error de conexión BD: " . $e->getMessage());
    die("Error: El sistema no está disponible en este momento.");
}
