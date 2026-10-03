<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/modelos/Baremo.php';

iniciar_sesion_segura();

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$userId = (int) $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF antes de procesar
    if (!validar_csrf()) {
        header('Location: dashboard.php?msg=baremo_error_csrf');
        exit();
    }

    try {
        if (isset($_POST['baremo']) && is_array($_POST['baremo'])) {
            Baremo::guardarRespuestas($pdo, $userId, $_POST['baremo']);
        }

        // Redirigir al dashboard con un mensaje de éxito
        // Redirigir al dashboard correspondiente al rol del usuario
        redirigir_por_rol((int)($_SESSION['rol'] ?? 0));
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error al guardar el baremo: " . $e->getMessage());
        die("Ocurrió un error al guardar el baremo.");
    }
} else {
    redirigir_por_rol((int)($_SESSION['rol'] ?? 0));
}