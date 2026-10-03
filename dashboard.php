<?php
/**
 * Router principal de Dashboard.
 * Redirige automáticamente al panel correspondiente según el rol del usuario autenticado.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

iniciar_sesion_segura();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?error=Debe+iniciar+sesión');
    exit();
}

$rutas = [
    1 => 'vistas/admin/dashboard.php',
    2 => 'vistas/coordinador/dashboard.php',
    3 => 'vistas/docente/dashboard.php',
    4 => 'vistas/secretaria/dashboard.php',
    5 => 'vistas/aspirante/dashboard.php',
    6 => 'vistas/estudiante/dashboard.php',
    7 => 'vistas/director/dashboard.php',
];

$rol_id = (int)($_SESSION['rol'] ?? $_SESSION['rol_id'] ?? 0);

if ($rol_id > 0 && isset($rutas[$rol_id])) {
    header('Location: ' . $rutas[$rol_id]);
    exit();
}

// Si el rol no está en sesión, consultarlo directamente a la BD
try {
    $stmt = $pdo->prepare("SELECT rol_id, estatus FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();

    if ($usuario && $usuario['estatus'] === 'Activo' && isset($rutas[(int)$usuario['rol_id']])) {
        $_SESSION['rol'] = (int)$usuario['rol_id'];
        $_SESSION['rol_id'] = (int)$usuario['rol_id'];
        header('Location: ' . $rutas[(int)$usuario['rol_id']]);
        exit();
    }
} catch (PDOException $e) {
    error_log("[SIP] Error en enrutador de dashboard: " . $e->getMessage());
}

// Si no se encuentra rol válido, redirigir al login
header('Location: index.php');
exit();