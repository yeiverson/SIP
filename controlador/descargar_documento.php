<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

iniciar_sesion_segura();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    exit('Acceso denegado');
}

$doc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$doc_id) {
    http_response_code(400);
    exit('ID requerido');
}

// Consultar documento
$stmt = $pdo->prepare("SELECT d.*, u.id as uid FROM aspirante_documentos d JOIN usuarios u ON u.id = d.usuario_id WHERE d.id = :id");
$stmt->execute([':id' => $doc_id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    exit('Documento no encontrado');
}

$rol = (int)($_SESSION['rol'] ?? 0);
$es_admin = in_array($rol, [1, 2, 4, 7], true);
$es_dueno = ((int)$_SESSION['usuario_id'] === (int)$doc['uid']);

if (!$es_admin && !$es_dueno) {
    http_response_code(403);
    exit('No autorizado para ver este documento');
}

$rutaBaseUploads = realpath(__DIR__ . '/../uploads');
$rutaArchivo = realpath(__DIR__ . '/../' . $doc['archivo_ruta']);

// Prevenir Path Traversal
if (!$rutaArchivo || !$rutaBaseUploads || !str_starts_with($rutaArchivo, $rutaBaseUploads) || !file_exists($rutaArchivo)) {
    http_response_code(404);
    exit('Archivo físico no encontrado en el servidor');
}

$ext = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
$mime = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];

$nombreDescarga = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $doc['archivo_nombre'] ?: basename($rutaArchivo));

header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . $nombreDescarga . '"');
header('Content-Length: ' . filesize($rutaArchivo));
header('X-Content-Type-Options: nosniff');
readfile($rutaArchivo);
exit;
