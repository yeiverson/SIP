<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode(['error' => 'Parámetro q requerido']);
    exit();
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
    echo json_encode($resultados);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta']);
}
