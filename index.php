<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    $rutas = [
        1 => 'vistas/admin/dashboard.php',
        2 => 'vistas/coordinador/dashboard.php',
        3 => 'vistas/docente/dashboard.php',
        4 => 'vistas/secretaria/dashboard.php',
        5 => 'vistas/aspirante/dashboard.php',
        6 => 'vistas/estudiante/dashboard.php',
        7 => 'vistas/director/dashboard.php',
    ];
    $ruta = $rutas[$_SESSION['rol']] ?? 'Inicio.php';
    header("Location: $ruta");
    exit();
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIP-Postgrado | UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-inicio.css">
    <link rel="icon" href="imagenes/sip.ico">
    <style>
        .error-msg { background: #fee; color: #c00; padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; font-size: 0.72rem; text-align: center; border: 1px solid #fcc; }
        .success-msg { background: #efe; color: #080; padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; font-size: 0.72rem; text-align: center; border: 1px solid #afa; }
        .doc-helper { font-size: 0.6rem; color: #999; margin-top: 2px; }
    </style>
</head>
<body>
<div class="background-overlay">
    <header class="navbar">
        <div class="logo-container">
            <img src="imagenes/LOGO-1-1.png" alt="Logo UNEFA" class="logo-img">
        </div>
        <div class="nav-buttons">
            <a href="Inicio.php" class="btn-outline">Inicio de Sesión</a>
            <a href="registro.php" class="btn-filled">Registro</a>
        </div>
    </header>

    <main class="content-wrapper">
        <div class="hero-text">
            <span class="hero-tag">Sistema Académico</span>
            <h2>Excelencia Educativa,<br>Abierta al Pueblo</h2>
            <div class="hero-divider"></div>
            <p class="hero-sub">Accede al Sistema de Gestión Académica de Postgrado de la UNEFA.</p>
        </div>

        <section class="login-card">
            <h3>Bienvenidos</h3>
            <p class="subtitle">Ingresa tus credenciales para acceder al sistema.</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registro'])): ?>
                <div class="success-msg">✅ ¡Usuario creado con éxito! Ahora ingresa con tus credenciales.</div>
            <?php endif; ?>
            <?php if (isset($_GET['perfil'])): ?>
                <div class="success-msg">✅ Perfil guardado exitosamente.</div>
            <?php endif; ?>

            <form action="controlador/procesar_login.php" method="POST" autocomplete="on">
                <div class="input-group">
                    <label for="tipo_documento">Tipo de documento</label>
                    <select name="tipo_documento" id="tipo_documento" required onchange="toggleDocType()">
                        <option value="">Seleccione el tipo</option>
                        <option value="V">V - Venezolano</option>
                        <option value="E">E - Extranjero Residente</option>
                        <option value="P">P - Pasaporte</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="numero_documento">Número de Documento</label>
                    <input name="numero_documento" id="numero_documento" type="text" placeholder="Escribe tu número de cédula o pasaporte" required>
                    <div class="doc-helper" id="doc-helper">Solo números para V/E, alfanumérico para P</div>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input name="password" id="password" type="password" placeholder="Escribe tu contraseña" required>
                </div>

                <div class="form-footer">
                    <label class="remember-label"><input type="checkbox" checked> Recordar</label>
                    <a href="#">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-login">INGRESAR</button>
            </form>

            <footer class="card-footer">
                <p>SIP-Postgrado 2026 · UNEFA</p>
                <div class="footer-logos">
                    <img class="gob" src="imagenes/gob.png" alt="Gobierno">
                    <img class="batalla" src="imagenes/200.png" alt="200 Batalla">
                </div>
            </footer>
        </section>
    </main>
</div>

<script>
function toggleDocType() {
    const tipo = document.getElementById('tipo_documento').value;
    const input = document.getElementById('numero_documento');
    const helper = document.getElementById('doc-helper');
    if (tipo === 'P') {
        input.placeholder = 'Escribe tu pasaporte (ej: FR98765432)';
        input.pattern = '[A-Za-z0-9]+';
        helper.textContent = 'Pasaporte: letras y números, sin espacios';
    } else if (tipo) {
        input.placeholder = 'Escribe tu número de cédula';
        input.pattern = '\\d+';
        helper.textContent = 'Cédula: solo números, entre 7 y 10 dígitos';
    } else {
        input.placeholder = 'Escribe tu número de documento';
        input.removeAttribute('pattern');
        helper.textContent = 'Selecciona un tipo de documento primero';
    }
}
</script>
</body>
</html>
