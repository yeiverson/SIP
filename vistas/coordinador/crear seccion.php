<?php
session_start();
if ($_SESSION['rol'] !== 2) exit("Acceso denegado");
require_once '../../config/conexion.php';

// Consultar materias disponibles y profesores registrados para los select
$materias = $pdo->query("SELECT codigo, nombre FROM asignaturas")->fetchAll();
$profesores = $pdo->query("SELECT id, nombres, apellidos FROM usuarios WHERE rol_id = 3")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Código para insertar en tabla 'secciones' (materia_codigo, profesor_id, aula, horario)
    // ... aquí iría el INSERT en tu base de datos ...
}
?>