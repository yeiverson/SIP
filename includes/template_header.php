<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol_nombre = obtener_nombre_rol($_SESSION['rol'] ?? 0);
$nombre_user = $_SESSION['nombre_full'] ?? 'Usuario';
$titulo = $titulo ?? 'SIP-Postgrado';
$css_extra = $css_extra ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema Integral de Postgrado UNEFA">
    <title><?php echo h($titulo); ?> | SIP-Postgrado UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo h(asset_url('css/tu_estilo.css')); ?>">
    <link rel="stylesheet" href="<?php echo h(asset_url('css/dashboard.css')); ?>">
    <link rel="icon" href="<?php echo h(asset_url('imagenes/sip.ico')); ?>">
    <?php if (!empty($css_extra)): ?>
    <style><?php echo $css_extra; ?></style>
    <?php endif; ?>
    <!-- Anti-FOUC: aplica el tema antes del primer paint -->
    <script>
        (function() {
            try {
                var t = localStorage.getItem('sip_theme');
                if (t === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch(e) {}
        })();
    </script>
</head>
<body class="unefa-body">
<header class="unefa-topbar">
    <div class="container-brand">
        <img src="<?php echo h(asset_url('imagenes/LOGO-1-1.png')); ?>" alt="UNEFA" class="brand-mark">
        <div class="brand-copy">
            <strong>UNEFA</strong>
            <span>Vicerrectorado de Investigación, Postgrado y Recreación</span>
        </div>
    </div>
    <div class="container-program">
        <span class="eyebrow">Programa de Postgrado</span>
        <strong>SIP - Sistema Integral de Postgrado</strong>
    </div>
    <!-- Dark mode toggle -->
    <button id="btn-theme-toggle" class="btn-theme-toggle" title="Cambiar tema" aria-label="Cambiar tema claro/oscuro">🌙</button>
</header>
<script>
    // Dark mode toggle — persiste en localStorage
    (function() {
        var btn = document.getElementById('btn-theme-toggle');
        if (!btn) return;

        function getTheme() {
            try { return localStorage.getItem('sip_theme') || 'light'; } catch(e) { return 'light'; }
        }

        function setTheme(t) {
            try { localStorage.setItem('sip_theme', t); } catch(e) {}
            document.documentElement.setAttribute('data-theme', t);
            btn.textContent = (t === 'dark') ? '☀️' : '🌙';
            btn.title = (t === 'dark') ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro';
        }

        // Sincronizar al cargar
        setTheme(getTheme());

        btn.addEventListener('click', function() {
            setTheme(getTheme() === 'dark' ? 'light' : 'dark');
        });
    })();
</script>
