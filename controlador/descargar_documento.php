<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    die('Acceso denegado');
}

$doc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$doc_id) {
    http_response_code(400);
    die('ID requerido');
}

// Solo admin, secretaría o el propio aspirante pueden descargar
$stmt = $pdo->prepare("SELECT d.*, u.id as uid FROM aspirante_documentos d JOIN usuarios u ON u.id = d.usuario_id WHERE d.id = :id");
$stmt->execute([':id' => $doc_id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    die('Documento no encontrado');
}

$puede = ($_SESSION['rol'] == 4 || $_SESSION['rol'] == 1 || $_SESSION['usuario_id'] == $doc['uid']);
if (!$puede) {
    http_response_code(403);
    die('No autorizado');
}

$ruta = __DIR__ . '/../' . $doc['archivo_ruta'];
if (!file_exists($ruta)) {
    http_response_code(404);
    die('Archivo no encontrado en el servidor');
}

$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mime = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];

header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . basename($doc['archivo_nombre'] ?: $ruta) . '"');
header('Content-Length: ' . filesize($ruta));
readfile($ruta);
