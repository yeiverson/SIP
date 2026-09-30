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
</head>
<body class="unefa-body">
    <header class="unefa-topbar">
        <div class="container-brand">
            <img src="<?php echo h(asset_url('imagenes/logo-unefa.png')); ?>" alt="UNEFA" class="brand-mark">
            <div class="brand-copy">
                <strong>UNEFA</strong>
                <span>Vicerrectorado de Investigación, Postgrado y Recreación</span>
            </div>
        </div>

        <div class="container-program">
            <span class="eyebrow">Programa de Postgrado</span>
            <strong>SIP - Sistema Integral de Postgrado</strong>
        </div>
    </header>
