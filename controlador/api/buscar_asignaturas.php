<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/database.php';

$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;
$sede_id = isset($_GET['sede_id']) ? (int)$_GET['sede_id'] : 0;

if (!$plan_id) {
    echo json_encode(['error' => 'Parámetro plan_id requerido']);
    exit();
}

try {
    $sql = "SELECT a.codigo, a.nombre, a.uc, pa.semestre, pa.obligatoria
            FROM asignaturas a
            JOIN plan_asignaturas pa ON pa.asignatura_codigo = a.codigo
            WHERE pa.plan_id = :plan AND a.activa = true
            ORDER BY pa.semestre, a.nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':plan' => $plan_id]);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta']);
}
