<?php
/**
 * Template de header HTML para dashboards.
 * Requiere: $titulo, $rol_id (opcional)
 */
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
    <title><?php echo h($titulo); ?> | SIP-Postgrado UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo obtener_ruta_base(); ?>css/tu_estilo.css">
    <link rel="stylesheet" href="<?php echo obtener_ruta_base(); ?>css/dashboard.css">
    <link rel="icon" href="<?php echo obtener_ruta_base(); ?>imagenes/sip.ico">
    <?php if ($css_extra): ?>
    <style><?php echo $css_extra; ?></style>
    <?php endif; ?>
</head>
<body>
