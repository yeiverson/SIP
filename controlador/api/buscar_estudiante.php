<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/RateLimiter.php';
require_once __DIR__ . '/../../config/database.php';

RateLimiter::check('api', 60, 60);
iniciar_sesion_segura();

// Verificar autenticación y roles autorizados (Admin, Coordinador, Secretaría, Director)
if (!isset($_SESSION['usuario_id']) || !in_array((int)($_SESSION['rol'] ?? 0), [1, 2, 4, 7], true)) {
    json_respuesta(['error' => 'No autorizado'], 403);
}

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    json_respuesta(['error' => 'Parámetro q requerido'], 400);
}

$q = '%' . trim($_GET['q']) . '%';

try {
    $stmt = $pdo->prepare("SELECT id, tipo_cedula, numero_documento, nombres, apellidos, email, rol_id, estatus
            FROM usuarios
            WHERE (numero_documento ILIKE :q OR nombres ILIKE :q2 OR apellidos ILIKE :q3 OR email ILIKE :q4)
            AND estatus = 'Activo'
            ORDER BY apellidos, nombres
            LIMIT 20");
    $stmt->execute([':q' => $q, ':q2' => $q, ':q3' => $q, ':q4' => $q]);
    $resultados = $stmt->fetchAll();
    
    json_respuesta($resultados);
} catch (PDOException $e) {
    error_log("[SIP API] Error en buscar_estudiante: " . $e->getMessage());
    json_respuesta(['error' => 'Error al consultar la base de datos'], 500);
}
