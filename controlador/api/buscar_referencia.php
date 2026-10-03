<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/RateLimiter.php';
require_once __DIR__ . '/../../config/database.php';

RateLimiter::check('api', 60, 60);
iniciar_sesion_segura();

// Solo roles con permisos de tesorería/coordinación/secretaría/admin pueden verificar referencias de pagos
if (!isset($_SESSION['usuario_id']) || !in_array((int)($_SESSION['rol'] ?? 0), [1, 2, 4, 7], true)) {
    json_respuesta(['error' => 'No autorizado'], 403);
}

if (!isset($_GET['ref']) || trim($_GET['ref']) === '') {
    json_respuesta(['error' => 'Parámetro ref requerido'], 400);
}

$ref = trim($_GET['ref']);

try {
    $stmt = $pdo->prepare("SELECT p.*, u.nombres, u.apellidos, u.tipo_cedula, u.numero_documento,
            i.estatus as inscripcion_estatus
            FROM pagos p
            JOIN usuarios u ON u.id = p.usuario_id
            JOIN inscripciones i ON i.id = p.inscripcion_id
            WHERE p.referencia = :ref");
    $stmt->execute([':ref' => $ref]);
    $pago = $stmt->fetch();

    if ($pago) {
        json_respuesta(['encontrado' => true, 'pago' => $pago]);
    } else {
        json_respuesta(['encontrado' => false, 'mensaje' => 'Referencia no encontrada']);
    }
} catch (PDOException $e) {
    error_log("[SIP API] Error en buscar_referencia: " . $e->getMessage());
    json_respuesta(['error' => 'Error al consultar referencia'], 500);
}
