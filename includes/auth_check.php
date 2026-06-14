<?php
/**
 * Verificación de autenticación y roles.
 * Uso: require_once __DIR__ . '/includes/auth_check.php';
 *      check_auth();             // Solo requiere login
 *      check_rol(1);             // Solo Admin
 *      check_rol([1,2,7]);       // Admin, Coordinador o Director
 */

function check_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . obtener_ruta_base() . 'index.php?error=Debe+iniciar+sesión');
        exit();
    }
}

function check_rol($roles_permitidos) {
    check_auth();
    $roles = is_array($roles_permitidos) ? $roles_permitidos : [$roles_permitidos];
    if (!in_array($_SESSION['rol'], $roles)) {
        header('Location: ' . obtener_ruta_base() . 'index.php?error=Acceso+no+autorizado');
        exit();
    }
}

function obtener_ruta_base() {
    $niveles = substr_count($_SERVER['SCRIPT_NAME'], '/') - 1;
    return str_repeat('../', $niveles);
}

function obtener_nombre_rol($rol_id) {
    $mapa = [
        1 => 'Administrador',
        2 => 'Coordinador',
        3 => 'Docente',
        4 => 'Secretaría',
        5 => 'Aspirante',
        6 => 'Estudiante',
        7 => 'Director',
    ];
    return $mapa[$rol_id] ?? 'Desconocido';
}

function redirigir_por_rol($rol_id) {
    $base = obtener_ruta_base();
    $rutas = [
        1 => $base . 'vistas/admin/dashboard.php',
        2 => $base . 'vistas/coordinador/dashboard.php',
        3 => $base . 'vistas/docente/dashboard.php',
        4 => $base . 'vistas/secretaria/dashboard.php',
        5 => $base . 'vistas/aspirante/dashboard.php',
        6 => $base . 'vistas/estudiante/dashboard.php',
        7 => $base . 'vistas/director/dashboard.php',
    ];
    header('Location: ' . ($rutas[$rol_id] ?? $base . 'index.php'));
    exit();
}
