<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['ref']) || trim($_GET['ref']) === '') {
    echo json_encode(['error' => 'Parámetro ref requerido']);
    exit();
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
        echo json_encode(['encontrado' => true, 'pago' => $pago]);
    } else {
        echo json_encode(['encontrado' => false, 'mensaje' => 'Referencia no encontrada']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta']);
}
