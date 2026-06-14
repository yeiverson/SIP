<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
if (!$uid) {
    echo json_encode(['error' => 'uid requerido']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, tipo, archivo_ruta, archivo_nombre, verificado, observaciones, created_at
            FROM aspirante_documentos WHERE usuario_id = :uid ORDER BY tipo");
    $stmt->execute([':uid' => $uid]);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar documentos']);
}
