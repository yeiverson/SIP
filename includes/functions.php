<?php
/**
 * Funciones helpers compartidas.
 */

function h($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function solo_numeros($valor) {
    return preg_replace('/\D/', '', (string)$valor);
}

function formatear_cedula($tipo, $numero) {
    return $tipo . '-' . number_format((int)$numero, 0, '', '.');
}

function formatear_fecha($fecha) {
    if (!$fecha) return '—';
    $timestamp = strtotime($fecha);
    return date('d/m/Y', $timestamp);
}

function obtener_meses() {
    return ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
            'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
}

function obtener_dias() {
    return ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
}

function fecha_hoy_formateada() {
    $dias = obtener_dias();
    $meses = obtener_meses();
    return $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');
}

function escapar_texto_multilinea($texto) {
    return nl2br(h($texto));
}

function alerta_success($mensaje) {
    return "<div class='alert alert-success'>✅ " . h($mensaje) . "</div>";
}

function alerta_error($mensaje) {
    return "<div class='alert alert-error'>❌ " . h($mensaje) . "</div>";
}

function alerta_warning($mensaje) {
    return "<div class='alert alert-warning'>⚠️ " . h($mensaje) . "</div>";
}

function alerta_info($mensaje) {
    return "<div class='alert alert-info'>ℹ️ " . h($mensaje) . "</div>";
}

/**
 * RUTAS Y ASSETS
 * - obtener_ruta_base(): devuelve la URL base de la aplicación (con slash final).
 *   Prioriza: constante BASE_URL definida, $_SERVER['BASE_URL'], variable de entorno BASE_URL,
 *   y si no existe intenta inferirla desde el host y el script actual.
 * - asset_url($path): normaliza y concatena la ruta del asset.
 * - img_tag(...): retorna una etiqueta <img> segura con fallback si no existe el archivo.
 */
function obtener_ruta_base() {
    // 1) Constante explícita en código (por seguridad al desplegar)
    if (defined('BASE_URL') && BASE_URL) {
        $b = rtrim(BASE_URL, '/') . '/';
        return $b;
    }

    // 2) Variable de servidor (útil para servidores que la definen)
    if (!empty($_SERVER['BASE_URL'])) {
        return rtrim($_SERVER['BASE_URL'], '/') . '/';
    }

    // 3) Variable de entorno
    $env = getenv('BASE_URL');
    if ($env && $env !== false) {
        return rtrim($env, '/') . '/';
    }

    // 4) Inferir desde host y script
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '/');

    // Si hay un index.php en la ruta, asumimos que ese es el front controller y regresamos hasta allí
    $indexPos = strpos($script, '/index.php');
    if ($indexPos !== false) {
        $basePath = substr($script, 0, $indexPos);
    } else {
        // De lo contrario, usamos el directorio del script actual como base aproximada
        $basePath = rtrim(dirname($script), '/\\');
    }

    $base = $scheme . '://' . $host . ($basePath === '/' ? '' : $basePath) . '/';
    return $base;
}

function asset_url($path) {
    $base = obtener_ruta_base();
    return $base . ltrim($path, '/');
}

function ruta_fisica_desde_url($url) {
    // Intenta resolver una URL pública a una ruta de archivo en disco usando DOCUMENT_ROOT
    $doc = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
    if (!$doc) return null;
    $parts = parse_url($url);
    $path = $parts['path'] ?? '/';
    return $doc . $path;
}

function fallback_image_url() {
    // Preferencia: imagen por defecto en /imagenes/default.png
    $default = 'imagenes/default.png';
    $url = asset_url($default);
    $fisica = ruta_fisica_desde_url($url);
    if ($fisica && file_exists($fisica)) return $url;

    // Si no existe, devolvemos un SVG embebido (pequeño placeholder)
    $svg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="180" viewBox="0 0 320 180"><rect width="100%" height="100%" fill="#F8F9FA"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#333" font-family="Arial,Helvetica,sans-serif" font-size="14">Imagen no disponible</text></svg>');
    return 'data:image/svg+xml;utf8,' . $svg;
}

function img_tag($src, $alt = '', $attrs = '') {
    $url = asset_url($src);
    $fisica = ruta_fisica_desde_url($url);
    if (!($fisica && file_exists($fisica))) {
        $url = fallback_image_url();
    }
    $alt_safe = h($alt);
    return '<img src="' . h($url) . '" alt="' . $alt_safe . '" ' . $attrs . '>'; 
}

?>