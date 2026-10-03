<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

iniciar_sesion_segura();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro SIP-Postgrado - UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo h(asset_url('style-registro.css')); ?>">
    <link rel="icon" href="<?php echo h(asset_url('imagenes/sip.ico')); ?>">
</head>
<body>

    <header class="navbar-header">
        <div class="header-content">
            <div class="logo-container">
                <img src="<?php echo h(asset_url('imagenes/LOGO-1-1.png')); ?>" alt="Logo UNEFA" class="logo-img">
            </div>
            <div class="nav-buttons">
                <a href="index.php" class="btn-primary">Inicio de Sesión</a>
                <a href="registro.php" class="btn-outline active">Registro</a>
            </div>
        </div>
    </header>

    <main class="main-container">
        <div class="header-titles">
            <h2>Registro SIP-Postgrado</h2>
            <p>Complete los campos a continuación para crear su perfil de usuario y dar el primer paso hacia su especialización profesional en nuestra casa de estudios.</p>
        </div>

        <div class="progress-container progress-registro-centrado">
            <div class="progress-bar progress-bar-registro" id="progress-bar">
                <div class="progress-step-column">
                    <div class="progress-step active" data-step="1">1</div>
                    <span class="progress-step-caption">Registre su usuario</span>
                </div>
            </div>
        </div>

        <section class="registration-card">
            <form id="multi-step-form" action="procesar_registro.php" method="POST" data-registro-ajax="1">
                <?php echo csrf_field(); ?>
                
                <div class="form-step active" id="step-1">
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label for="tipoDocumento">Tipo de documento:</label>
                            <select name="tipoDocumento" id="tipoDocumento" required onchange="toggleRegDocType()">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="V">V — Venezolano</option>
                                <option value="E">E — Extranjero</option>
                                <option value="P">P — Pasaporte</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="reg-cedula" id="doc-label">Cédula / Pasaporte:</label>
                            <input type="text" name="cedula" id="reg-cedula" placeholder="Número de cédula o pasaporte" required>
                        </div>
                    </div>
                    
                    <div class="form-grid-2" style="margin-top: 16px;">
                        <div class="input-group">
                            <label for="email">Correo electrónico:</label>
                            <input type="email" name="email" id="email" placeholder="tu.correo@ejemplo.com" required>
                        </div>
                    </div>

                    <div class="password-section" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #eee;">
                        <h4 style="margin-bottom: 15px; color: #333;">Seguridad de la Cuenta</h4>
                        <div class="form-grid-2">
                            <div class="input-group">
                                <label for="password">Crea tu Contraseña:</label>
                                <input type="password" name="password" id="password" required placeholder="Mínimo 8 caracteres (1 mayúscula)">
                            </div>
                            <div class="input-group">
                                <label for="confirm_password">Confirma tu Contraseña:</label>
                                <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repite tu clave">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-navigation" style="margin-top: 30px; display: flex; justify-content: flex-end;">
                    <button type="button" id="next-btn" class="btn-primary">Crear usuario</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-divider"></div>
            <div class="footer-text">
                <p>UNEFA | Excelencia Educativa Abierta al Pueblo</p>
                <p>Vicerrectorado de Investigación, Postgrado y Recreación</p>
            </div>
        </div>
    </footer>

    <script>
    function toggleRegDocType() {
        const tipo = document.getElementById('tipoDocumento');
        const cedulaInput = document.getElementById('reg-cedula');
        const label = document.getElementById('doc-label');
        if (!tipo || !cedulaInput) return;
        if (tipo.value === 'P') {
            cedulaInput.placeholder = 'Ej: FR98765432 (alfanumérico)';
            cedulaInput.pattern = '[A-Za-z0-9]{4,20}';
            if (label) label.textContent = 'Pasaporte:';
        } else if (tipo.value) {
            cedulaInput.placeholder = 'Solo números (6 a 8 dígitos)';
            cedulaInput.pattern = '\\d{6,8}';
            if (label) label.textContent = 'Cédula de Identidad:';
        } else {
            cedulaInput.placeholder = 'Número de cédula o pasaporte';
            cedulaInput.removeAttribute('pattern');
            if (label) label.textContent = 'Cédula / Pasaporte:';
        }
    }
    </script>
    <script src="<?php echo h(asset_url('multi-step-form.js')); ?>"></script>
</body>
</html>