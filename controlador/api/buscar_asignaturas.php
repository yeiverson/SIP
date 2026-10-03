<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/RateLimiter.php';
require_once __DIR__ . '/../../config/database.php';

RateLimiter::check('api', 60, 60);
iniciar_sesion_segura();

// Exige usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    json_respuesta(['error' => 'No autenticado'], 401);
}

$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;
$sede_id = isset($_GET['sede_id']) ? (int)$_GET['sede_id'] : 0;

if (!$plan_id) {
    json_respuesta(['error' => 'Parámetro plan_id requerido'], 400);
}

try {
    $sql = "SELECT a.codigo, a.nombre, a.uc, pa.semestre, pa.obligatoria
            FROM asignaturas a
            JOIN plan_asignaturas pa ON pa.asignatura_codigo = a.codigo
            WHERE pa.plan_id = :plan AND a.activa = true
            ORDER BY pa.semestre, a.nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':plan' => $plan_id]);
    json_respuesta($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("[SIP API] Error en buscar_asignaturas: " . $e->getMessage());
    json_respuesta(['error' => 'Error al consultar asignaturas'], 500);
}
