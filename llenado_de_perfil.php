<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

iniciar_sesion_segura();

$userId = $_SESSION['usuario_id'] ?? 0;
$rol = (int)($_SESSION['rol'] ?? 0);

// Si no tiene sesión activa, al login
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

// Si ya tiene nombres completos y no son "Pendiente", permitir ir directo al dashboard
if ($user['nombres'] !== 'Pendiente' && $user['apellidos'] !== 'Pendiente' && $rol >= 1 && $rol <= 7 && empty($_GET['editar'])) {
    $rutas = [
        1 => 'vistas/admin/dashboard.php',
        2 => 'vistas/coordinador/dashboard.php',
        3 => 'vistas/docente/dashboard.php',
        4 => 'vistas/secretaria/dashboard.php',
        5 => 'vistas/aspirante/dashboard.php',
        6 => 'vistas/estudiante/dashboard.php',
        7 => 'vistas/director/dashboard.php',
    ];
    header('Location: ' . ($rutas[$rol] ?? 'vistas/aspirante/dashboard.php'));
    exit();
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $error = 'Token de seguridad inválido. Por favor recargue el formulario.';
    } else {
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $telefono = solo_numeros($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');

        if (empty($nombres) || empty($apellidos)) {
            $error = 'Nombres y apellidos son campos obligatorios.';
        } elseif (!empty($telefono) && (strlen($telefono) !== 11 || substr($telefono, 0, 1) !== '0')) {
            $error = 'El número de teléfono debe tener 11 dígitos e iniciar con 0 (Ej: 04121234567).';
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
                $_SESSION['rol'] = (int)($user['rol_id'] ?: 5);

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
                if ((string)$e->getCode() === '23505') {
                    $error = 'El número de teléfono ya está en uso por otro usuario.';
                } else {
                    error_log('[SIP] Error al guardar perfil: ' . $e->getMessage());
                    $error = 'Hubo un error al actualizar los datos en el servidor.';
                }
            }
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
    <link rel="stylesheet" href="<?php echo h(asset_url('style-registro.css')); ?>">
    <link rel="icon" href="<?php echo h(asset_url('imagenes/sip.ico')); ?>">
    <style>
        .msg-error { background: #fee; color: #c00; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fcc; font-size: 0.85rem; }
        .msg-success { background: #efe; color: #080; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #afa; font-size: 0.85rem; }
    </style>
</head>
<body>
    <header class="navbar-header">
        <div class="header-content">
            <div class="logo-container">
                <img src="<?php echo h(asset_url('imagenes/LOGO-1-1.png')); ?>" alt="Logo UNEFA" class="logo-img">
            </div>
            <div class="nav-buttons">
                <a href="index.php" class="btn-primary">Inicio</a>
            </div>
        </div>
    </header>

    <main class="main-container" style="max-width:640px;margin:40px auto;">
        <section class="registration-card">
            <h3>Completar Perfil</h3>
            <p>Antes de continuar al panel, ingresa tus nombres y apellidos reales para la emisión de documentos académicos.</p>

            <?php if ($error): ?>
                <div class="msg-error"><?php echo h($error); ?></div>
            <?php endif; ?>
            <?php if ($mensaje): ?>
                <div class="msg-success"><?php echo h($mensaje); ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-grid-2">
                    <div class="input-group">
                        <label>Tipo Documento:</label>
                        <input type="text" value="<?php echo h($user['tipo_cedula'] . '-' . $user['numero_documento']); ?>" disabled style="background:#f0f0f0;">
                    </div>
                    <div class="input-group">
                        <label>Email:</label>
                        <input type="text" value="<?php echo h($user['email'] ?? ''); ?>" disabled style="background:#f0f0f0;">
                    </div>
                    <div class="input-group">
                        <label for="nombres">Nombres:</label>
                        <input type="text" id="nombres" name="nombres" value="<?php echo h($user['nombres'] !== 'Pendiente' ? $user['nombres'] : ''); ?>" required placeholder="Ej: Juan Carlos">
                    </div>
                    <div class="input-group">
                        <label for="apellidos">Apellidos:</label>
                        <input type="text" id="apellidos" name="apellidos" value="<?php echo h($user['apellidos'] !== 'Pendiente' ? $user['apellidos'] : ''); ?>" required placeholder="Ej: Pérez Rodríguez">
                    </div>
                    <div class="input-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo h($user['telefono'] ?? ''); ?>" placeholder="Ej: 04121234567">
                    </div>
                    <div class="input-group">
                        <label for="direccion">Dirección / Ciudad:</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo h($user['direccion'] !== 'Pendiente' ? $user['direccion'] : ''); ?>" placeholder="Ej: Caracas, Dtto Capital">
                    </div>
                </div>
                <button type="submit" class="btn-login" style="margin-top:20px;width:100%;cursor:pointer;">Guardar y Continuar</button>
            </form>
        </section>
    </main>
</body>
</html>
