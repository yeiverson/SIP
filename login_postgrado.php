<?php
declare(strict_types=1);

session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inicio.php');
    exit();
}

// Normalizamos y validamos entrada
$tipo_cedula = isset($_POST['tipo_cedula']) ? trim((string)$_POST['tipo_cedula']) : '';
$cedula_raw  = isset($_POST['cedula']) ? trim((string)$_POST['cedula']) : '';
$pass_ingresada = (string)($_POST['password'] ?? '');

// Dejamos sólo números en la cédula para evitar caracteres extraños
$cedula_limpia = preg_replace('/\D/', '', $cedula_raw);

if ($tipo_cedula === '' || $cedula_limpia === '' || strlen($cedula_limpia) < 7 || strlen($cedula_limpia) > 10) {
    header('Location: inicio.php?error=datos_invalidos');
    exit();
}

if ($pass_ingresada === '') {
    header('Location: inicio.php?error=datos_invalidos');
    exit();
}

try {
    // Buscamos por cédula y tipo de documento para ser más estrictos
    $sql = 'SELECT id, nombres, password, tipo_cedula, cedula 
            FROM usuarios 
            WHERE cedula = :ci AND tipo_cedula = :tipo
            LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ci'   => $cedula_limpia,
        ':tipo' => $tipo_cedula,
    ]);

    $usuario = $stmt->fetch();

    // Mensaje genérico siempre, para no revelar si el usuario existe o no
    $error_redirect = 'Location: inicio.php?error=datos_invalidos';

    if (!$usuario || !password_verify($pass_ingresada, $usuario['password'])) {
        // Pequeña pausa para dificultar ataques de fuerza bruta masivos
        usleep(300000); // 0.3 segundos
        header($error_redirect);
        exit();
    }

// Éxito: regeneramos el ID de sesión para evitar fijación de sesión
    session_regenerate_id(true);
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['nombre']  = $usuario['nombres'];

    // CAMBIO AQUÍ: Redirigimos al dashboard en lugar del llenado de perfil
    header('Location: dashboard.php');
    exit();

} catch (PDOException $error) {
    // Registramos el error en el log del servidor, pero no lo mostramos al usuario
    error_log('Error de login: ' . $error->getMessage());
    header('Location: inicio.php?error=servidor');
    exit();
}
