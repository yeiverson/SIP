<?php
/**
 * Funciones helpers compartidas.
 */

function h($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function solo_numeros($valor) {
    return preg_replace('/\D/', '', (string) $valor);
}

function formatear_cedula($tipo, $numero) {
    return $tipo . '-' . number_format((int) $numero, 0, '', '.');
}

function formatear_fecha($fecha) {
    if (!$fecha) {
        return '—';
    }

    $timestamp = strtotime((string) $fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : '—';
}

function obtener_meses() {
    return ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
}

function obtener_dias() {
    return ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
}

function fecha_hoy_formateada() {
    $dias = obtener_dias();
    $meses = obtener_meses();
    return $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n') - 1] . ' de ' . date('Y');
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
 * - obtener_ruta_base(): devuelve la URL base de la aplicación con slash final.
 * - asset_url($path): normaliza y concatena la URL del asset.
 * - img_tag(...): retorna una etiqueta <img> segura con fallback si el archivo no existe.
 */
if (!function_exists('obtener_ruta_base')) {
    function obtener_ruta_base() {
        if (defined('BASE_URL') && BASE_URL) {
            return rtrim((string) BASE_URL, '/') . '/';
        }

        if (!empty($_SERVER['BASE_URL'])) {
            return rtrim((string) $_SERVER['BASE_URL'], '/') . '/';
        }

        $env = getenv('BASE_URL');
        if ($env && $env !== false) {
            return rtrim((string) $env, '/') . '/';
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '/');

        $indexPos = strpos($script, '/index.php');
        if ($indexPos !== false) {
            $basePath = substr($script, 0, $indexPos);
        } else {
            $basePath = rtrim(dirname($script), '/\\');
        }

        $base = $scheme . '://' . $host . ($basePath === '/' ? '' : $basePath) . '/';
        return $base;
    }
}

if (!function_exists('asset_url')) {
    function asset_url($path) {
        $base = obtener_ruta_base();
        return $base . ltrim((string) $path, '/');
    }
}

if (!function_exists('ruta_fisica_desde_url')) {
    function ruta_fisica_desde_url($url) {
        $doc = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
        if (!$doc) {
            return null;
        }

        $parts = parse_url((string) $url);
        $path = $parts['path'] ?? '/';
        return $doc . $path;
    }
}

if (!function_exists('fallback_image_url')) {
    function fallback_image_url() {
        $default = 'imagenes/default.png';
        $url = asset_url($default);
        $fisica = ruta_fisica_desde_url($url);
        if ($fisica && file_exists($fisica)) {
            return $url;
        }

        $svg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="180" viewBox="0 0 320 180"><rect width="100%" height="100%" fill="#F8F9FA"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#333333" font-family="Arial,Helvetica,sans-serif" font-size="14">Imagen no disponible</text></svg>');
        return 'data:image/svg+xml;utf8,' . $svg;
    }
}

if (!function_exists('img_tag')) {
    function img_tag($src, $alt = '', $attrs = '') {
        $url = asset_url($src);
        $fisica = ruta_fisica_desde_url($url);
        if (!($fisica && file_exists($fisica))) {
            $url = fallback_image_url();
        }

        $altSafe = h($alt);
        return '<img src="' . h($url) . '" alt="' . $altSafe . '" ' . $attrs . '>';
    }
}

if (!function_exists('obtener_nombre_rol')) {
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
}

if (!function_exists('redirigir_por_rol')) {
    function redirigir_por_rol($rol_id) {
        $base = function_exists('obtener_ruta_base') ? obtener_ruta_base() : './';
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
}
