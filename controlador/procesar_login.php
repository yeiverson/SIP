<?php
// controlador/procesar_login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

iniciar_sesion_segura();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

// Validar token CSRF
$csrf_recibido = $_POST['csrf_token'] ?? '';
if (!validar_csrf($csrf_recibido)) {
    header('Location: ../index.php?error=Token+de+seguridad+inválido.+Por+favor+intente+de+nuevo');
    exit();
}

$tipo_documento   = strtoupper(trim($_POST['tipo_documento'] ?? ''));
$numero_documento = trim($_POST['numero_documento'] ?? '');
$password         = $_POST['password'] ?? '';

if (empty($tipo_documento) || empty($numero_documento) || empty($password)) {
    header('Location: ../index.php?error=Complete+todos+los+campos');
    exit();
}

try {
    // Buscar por tipo + número de documento
    $sql = "SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos,
                   u.password, u.rol_id, u.sede_id, u.estatus, u.estado_aspirante, u.email
            FROM usuarios u
            WHERE u.tipo_cedula = :tipo AND u.numero_documento = :doc
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tipo' => $tipo_documento, ':doc' => $numero_documento]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        usleep(250000); // 250ms delay contra ataques de temporización
        header('Location: ../index.php?error=Datos+de+acceso+inválidos');
        exit();
    }

    if ($usuario['estatus'] !== 'Activo') {
        header('Location: ../index.php?error=Cuenta+inactiva.+Consulte+a+Control+de+Estudios');
        exit();
    }

    // Regenerar ID de sesión por seguridad anti session fixation
    session_regenerate_id(true);

    $_SESSION['usuario_id']       = (int)$usuario['id'];
    $_SESSION['tipo_documento']   = $usuario['tipo_cedula'];
    $_SESSION['numero_documento'] = $usuario['numero_documento'];
    $_SESSION['identidad']        = $usuario['tipo_cedula'] . '-' . $usuario['numero_documento'];
    $_SESSION['nombre_full']      = trim($usuario['nombres'] . ' ' . $usuario['apellidos']);
    $_SESSION['email']            = $usuario['email'];
    $_SESSION['rol']              = (int)$usuario['rol_id'];
    $_SESSION['rol_id']           = (int)$usuario['rol_id'];
    $_SESSION['sede_id']          = $usuario['sede_id'] ? (int)$usuario['sede_id'] : null;
    $_SESSION['estado_aspirante'] = $usuario['estado_aspirante'];
    $_SESSION['csrf_token']       = bin2hex(random_bytes(32));

    // Registrar log de ingreso
    require_once __DIR__ . '/../includes/logs.php';
    registrar_log($pdo, 'Inicio de sesión', 'usuarios', $usuario['id'],
                  "Usuario {$_SESSION['nombre_full']} inició sesión correctamente");

    // Redirigir según rol
    $rutas = [
        1 => '../vistas/admin/dashboard.php',
        2 => '../vistas/coordinador/dashboard.php',
        3 => '../vistas/docente/dashboard.php',
        4 => '../vistas/secretaria/dashboard.php',
        5 => '../vistas/aspirante/dashboard.php',
        6 => '../vistas/estudiante/dashboard.php',
        7 => '../vistas/director/dashboard.php',
    ];

    $ruta = $rutas[(int)$usuario['rol_id']] ?? '../index.php';
    header('Location: ' . $ruta);
    exit();

} catch (PDOException $e) {
    error_log("[SIP] Error en login: " . $e->getMessage());
    header('Location: ../index.php?error=Error+en+el+servidor');
    exit();
}
