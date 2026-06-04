<?php
// config/conexion.php

// Datos de conexión de tu PostgreSQL actual (Ajusta estos valores si varían)
$host     = "localhost";
$port     = "5432";
$dbname   = "unefa_postgrados"; // <--- Pon aquí el nombre real de tu BD
$user     = "postgres";        // <--- Tu usuario de Postgres
$password = "yeiverson";   // <--- Tu contraseña de Postgres

try {
    // Estructura de conexión limpia para PostgreSQL usando PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    
    // Configuraciones de seguridad y manejo de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Conexión exitosa (Mantenlo comentado en producción, descoméntalo solo para probar)
    // echo "Conexión exitosa a la base de datos de Postgrado.";
    
} catch (PDOException $e) {
    // Si falla, detiene el sistema de forma segura sin exponer credenciales críticas
    die("Error crítico: No se pudo conectar con la base de datos institucional. " . $e->getMessage());
}