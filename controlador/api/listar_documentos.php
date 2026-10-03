<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/RateLimiter.php';
require_once __DIR__ . '/../../config/database.php';

RateLimiter::check('api', 60, 60);
iniciar_sesion_segura();

if (!isset($_SESSION['usuario_id'])) {
    json_respuesta(['error' => 'No autenticado'], 401);
}

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
if (!$uid) {
    json_respuesta(['error' => 'Parámetro uid requerido'], 400);
}

$sesion_uid = (int)$_SESSION['usuario_id'];
$rol = (int)($_SESSION['rol'] ?? 0);

// Solo el propio usuario o roles administrativos (1,2,4,7) pueden ver los documentos
if ($uid !== $sesion_uid && !in_array($rol, [1, 2, 4, 7], true)) {
    json_respuesta(['error' => 'Acceso denegado'], 403);
}

try {
    $stmt = $pdo->prepare("SELECT id, tipo, archivo_ruta, archivo_nombre, verificado, observaciones, created_at
            FROM aspirante_documentos WHERE usuario_id = :uid ORDER BY tipo");
    $stmt->execute([':uid' => $uid]);
    json_respuesta($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("[SIP API] Error en listar_documentos: " . $e->getMessage());
    json_respuesta(['error' => 'Error al consultar documentos'], 500);
}
