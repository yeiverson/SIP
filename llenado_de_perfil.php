<?php
session_start();
require_once __DIR__ . '/config/database.php';

$userId = $_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? 0;
$rol = $_SESSION['rol'] ?? 0;

// Si ya tiene rol definido, redirigir a su dashboard
if ($rol >= 1 && $rol <= 7) {
    $rutas = [
        1 => 'vistas/admin/dashboard.php',
        2 => 'vistas/coordinador/dashboard.php',
        3 => 'vistas/docente/dashboard.php',
        4 => 'vistas/secretaria/dashboard.php',
        5 => 'vistas/aspirante/dashboard.php',
        6 => 'vistas/estudiante/dashboard.php',
        7 => 'vistas/director/dashboard.php',
    ];
    header('Location: ' . $rutas[$rol]);
    exit();
}

// Si no tiene sesión, al login
if (!$userId) {
    header('Location: index.php');
    exit();
}

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT id, tipo_cedula, numero_documento, nombres, apellidos, email, telefono, direccion, rol_id, estatus FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit();
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombres) || empty($apellidos)) {
        $error = 'Nombres y apellidos son obligatorios.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombres = :nom, apellidos = :ape, telefono = :tel, direccion = :dir WHERE id = :id");
            $stmt->execute([
                ':nom' => $nombres,
                ':ape' => $apellidos,
                ':tel' => $telefono ?: null,
                ':dir' => $direccion ?: null,
                ':id'  => $userId,
            ]);

            $_SESSION['nombre_full'] = $nombres . ' ' . $apellidos;
            $_SESSION['rol'] = $user['rol_id'] ?: 5; // aspirant by default

            $rutas_redirect = [
                1 => 'vistas/admin/dashboard.php',
                2 => 'vistas/coordinador/dashboard.php',
                3 => 'vistas/docente/dashboard.php',
                4 => 'vistas/secretaria/dashboard.php',
                5 => 'vistas/aspirante/dashboard.php',
                6 => 'vistas/estudiante/dashboard.php',
                7 => 'vistas/director/dashboard.php',
            ];
            $destino = $rutas_redirect[$_SESSION['rol']] ?? 'vistas/aspirante/dashboard.php';
            header("Location: $destino?perfil=ok");
            exit();
        } catch (PDOException $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil | SIP-Postgrado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-registro.css">
    <link rel="icon" href="imagenes/sip.ico">
    <style>
        .msg-error { background: #fee; color: #c00; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fcc; font-size: 0.85rem; }
        .msg-success { background: #efe; color: #080; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #afa; font-size: 0.85rem; }
    </style>
</head>
<body>
    <header class="navbar-header">
        <div class="header-content">
            <div class="logo-container">
                <img src="imagenes/LOGO-1-1.png" alt="Logo UNEFA" class="logo-img">
            </div>
            <div class="nav-buttons">
                <a href="index.php" class="btn-primary">Inicio</a>
            </div>
        </div>
    </header>

    <main class="main-container" style="max-width:600px;margin:40px auto;">
        <section class="registration-card">
            <h3>Completar Perfil</h3>
            <p>Antes de continuar, completa tus datos personales.</p>

            <?php if ($error): ?>
                <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($mensaje): ?>
                <div class="msg-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid-2">
                    <div class="input-group">
                        <label>Tipo Documento:</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['tipo_cedula'] . '-' . $user['numero_documento']); ?>" disabled>
                    </div>
                    <div class="input-group">
                        <label>Email:</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                    </div>
                    <div class="input-group">
                        <label>Nombres:</label>
                        <input type="text" name="nombres" value="<?php echo htmlspecialchars($user['nombres'] !== 'Pendiente' ? $user['nombres'] : ''); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Apellidos:</label>
                        <input type="text" name="apellidos" value="<?php echo htmlspecialchars($user['apellidos'] !== 'Pendiente' ? $user['apellidos'] : ''); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>" placeholder="04121234567">
                    </div>
                    <div class="input-group">
                        <label>Dirección:</label>
                        <input type="text" name="direccion" value="<?php echo htmlspecialchars($user['direccion'] !== 'Pendiente' ? $user['direccion'] : ''); ?>" placeholder="Ciudad, Estado">
                    </div>
                </div>
                <button type="submit" class="btn-login" style="margin-top:20px;width:100%;">Guardar y Continuar</button>
            </form>
        </section>
    </main>
</body>
</html>
