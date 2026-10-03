<?php
/**
 * Funciones helpers y de seguridad compartidas del SIP-Postgrado UNEFA.
 */

/**
 * Iniciar sesión de forma segura con cookies HTTPOnly, SameSite y Secure (si aplica HTTPS).
 */
if (!function_exists('iniciar_sesion_segura')) {
    function iniciar_sesion_segura(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }
    }
}

/**
 * Generar u obtener el token CSRF de la sesión activa.
 */
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        iniciar_sesion_segura();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Genera el campo oculto HTML con el token CSRF.
 */
if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
    }
}

/**
 * Valida el token CSRF recibido contra la sesión.
 */
if (!function_exists('validar_csrf')) {
    function validar_csrf(?string $token = null): bool {
        iniciar_sesion_segura();
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Escapar HTML de forma segura contra XSS.
 */
if (!function_exists('h')) {
    function h($texto): string {
        return htmlspecialchars((string)($texto ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Registra un mensaje flash en la sesión para mostrarlo en la siguiente petición.
 *
 * @param string $tipo    'success' | 'error' | 'warning' | 'info'
 * @param string $mensaje Texto del mensaje.
 */
if (!function_exists('flash')) {
    function flash(string $tipo, string $mensaje): void {
        iniciar_sesion_segura();
        $_SESSION['_flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }
}

/**
 * Renderiza y limpia todos los mensajes flash de la sesión.
 * Devuelve HTML listo para insertar en la vista.
 */
if (!function_exists('render_flash')) {
    function render_flash(): string {
        iniciar_sesion_segura();
        if (empty($_SESSION['_flash'])) {
            return '';
        }

        $mapa = [
            'success' => ['alerta-success', '✅'],
            'error'   => ['alerta-error',   '❌'],
            'warning' => ['alerta-warning', '⚠️'],
            'info'    => ['alerta-info',    'ℹ️'],
        ];

        $html = '';
        foreach ($_SESSION['_flash'] as $item) {
            $tipo    = $item['tipo'] ?? 'info';
            [$clase, $icono] = $mapa[$tipo] ?? $mapa['info'];
            $html .= '<div class="alerta ' . $clase . '" role="alert">'
                   . '<span class="alerta-icon">' . $icono . '</span>'
                   . '<span>' . h($item['mensaje']) . '</span>'
                   . '</div>';
        }
        unset($_SESSION['_flash']);

        return $html;
    }
}

/**
 * Extraer solo números de una cadena.
 */
if (!function_exists('solo_numeros')) {
    function solo_numeros($valor): string {
        return preg_replace('/\D/', '', (string)($valor ?? ''));
    }
}

/**
 * Validar cédula venezolana (V/E con 6 a 8 dígitos) o pasaporte (P de 4 a 20 alfanumérico).
 */
if (!function_exists('validar_documento_identidad')) {
    function validar_documento_identidad(string $tipo, string $numero): bool {
        $tipo = strtoupper(trim($tipo));
        $numero = trim($numero);
        if ($tipo === 'P') {
            return (bool) preg_match('/^[A-Za-z0-9]{4,20}$/', $numero);
        }
        if (in_array($tipo, ['V', 'E'], true)) {
            $nums = solo_numeros($numero);
            return strlen($nums) >= 6 && strlen($nums) <= 8;
        }
        return false;
    }
}

/**
 * Formatear cédula para visualización amigable (Ej: V-12.345.678).
 */
if (!function_exists('formatear_cedula')) {
    function formatear_cedula($tipo, $numero): string {
        $nums = solo_numeros($numero);
        if (empty($nums)) return (string)$numero;
        return strtoupper((string)$tipo) . '-' . number_format((int)$nums, 0, '', '.');
    }
}

/**
 * Formatear fecha a formato d/m/Y.
 */
if (!function_exists('formatear_fecha')) {
    function formatear_fecha($fecha): string {
        if (!$fecha) return '—';
        $timestamp = strtotime((string)$fecha);
        return $timestamp ? date('d/m/Y', $timestamp) : '—';
    }
}

if (!function_exists('obtener_meses')) {
    function obtener_meses(): array {
        return ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    }
}

if (!function_exists('obtener_dias')) {
    function obtener_dias(): array {
        return ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    }
}

if (!function_exists('fecha_hoy_formateada')) {
    function fecha_hoy_formateada(): string {
        $dias = obtener_dias();
        $meses = obtener_meses();
        return $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n') - 1] . ' de ' . date('Y');
    }
}

if (!function_exists('escapar_texto_multilinea')) {
    function escapar_texto_multilinea($texto): string {
        return nl2br(h($texto));
    }
}

if (!function_exists('alerta_success')) {
    function alerta_success(string $mensaje): string {
        return "<div class='alert alert-success' role='alert'>✅ " . h($mensaje) . "</div>";
    }
}

if (!function_exists('alerta_error')) {
    function alerta_error(string $mensaje): string {
        return "<div class='alert alert-error' role='alert'>❌ " . h($mensaje) . "</div>";
    }
}

if (!function_exists('alerta_warning')) {
    function alerta_warning(string $mensaje): string {
        return "<div class='alert alert-warning' role='alert'>⚠️ " . h($mensaje) . "</div>";
    }
}

if (!function_exists('alerta_info')) {
    function alerta_info(string $mensaje): string {
        return "<div class='alert alert-info' role='alert'>ℹ️ " . h($mensaje) . "</div>";
    }
}

/**
 * Helper para respuestas JSON estandarizadas.
 */
if (!function_exists('json_respuesta')) {
    function json_respuesta(array $datos, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Validar subida segura de archivos comprobando MIME real con finfo.
 */
if (!function_exists('validar_archivo_subido')) {
    function validar_archivo_subido(array $file, array $mimes_permitidos = ['application/pdf', 'image/jpeg', 'image/png'], int $max_mb = 5): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valido' => false, 'error' => 'Parámetros de subida inválidos.'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'error' => 'Error al subir el archivo (código: ' . $file['error'] . ').'];
        }
        
        if ($file['size'] > ($max_mb * 1024 * 1024)) {
            return ['valido' => false, 'error' => "El archivo supera el tamaño máximo permitido ({$max_mb}MB)."];
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime, $mimes_permitidos, true)) {
            return ['valido' => false, 'error' => 'Tipo de archivo no permitido. Solo se aceptan PDF, JPG o PNG.'];
        }
        
        return ['valido' => true, 'mime' => $mime];
    }
}

/**
 * RUTAS Y ASSETS
 */
if (!function_exists('obtener_ruta_base')) {
    function obtener_ruta_base(): string {
        if (defined('BASE_URL') && BASE_URL) {
            return rtrim((string)BASE_URL, '/') . '/';
        }

        if (!empty($_SERVER['BASE_URL'])) {
            return rtrim((string)$_SERVER['BASE_URL'], '/') . '/';
        }

        $env = getenv('BASE_URL');
        if ($env && $env !== false) {
            return rtrim((string)$env, '/') . '/';
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..') ?: dirname(__DIR__)), '/');
        $docRoot     = !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']), '/') : '';
        $scriptName  = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));
        $scriptFile  = !empty($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']) : '';

        $basePath = '';
        if ($docRoot && stripos($projectRoot, $docRoot) === 0) {
            $basePath = substr($projectRoot, strlen($docRoot));
        } elseif ($scriptFile && $projectRoot && stripos($scriptFile, $projectRoot) === 0) {
            $relScript = substr($scriptFile, strlen($projectRoot));
            if ($relScript !== '' && (substr($scriptName, -strlen($relScript)) === $relScript)) {
                $basePath = substr($scriptName, 0, -strlen($relScript));
            }
        } else {
            $folderName = '/' . basename($projectRoot);
            $pos = stripos($scriptName, $folderName);
            if ($pos !== false) {
                $basePath = substr($scriptName, 0, $pos + strlen($folderName));
            }
        }

        $basePath = '/' . trim($basePath, '/');
        if ($basePath === '/') {
            $basePath = '';
        }

        return $scheme . '://' . $host . $basePath . '/';
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string {
        $base = obtener_ruta_base();
        return $base . ltrim($path, '/');
    }
}

if (!function_exists('ruta_fisica_desde_url')) {
    function ruta_fisica_desde_url(string $url): ?string {
        $doc = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
        if (!$doc) return null;
        $parts = parse_url($url);
        $path = $parts['path'] ?? '/';
        return $doc . $path;
    }
}

if (!function_exists('fallback_image_url')) {
    function fallback_image_url(): string {
        $default = 'imagenes/default.png';
        $url = asset_url($default);
        $fisica = ruta_fisica_desde_url($url);
        if ($fisica && file_exists($fisica)) return $url;

        $svg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="180" viewBox="0 0 320 180"><rect width="100%" height="100%" fill="#F8F9FA"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#333" font-family="Arial,Helvetica,sans-serif" font-size="14">Imagen no disponible</text></svg>');
        return 'data:image/svg+xml;utf8,' . $svg;
    }
}

if (!function_exists('img_tag')) {
    function img_tag(string $src, string $alt = '', string $attrs = ''): string {
        $url = asset_url($src);
        $fisica = ruta_fisica_desde_url($url);
        if (!($fisica && file_exists($fisica))) {
            $url = fallback_image_url();
        }
        return '<img src="' . h($url) . '" alt="' . h($alt) . '" ' . $attrs . '>'; 
    }
}

/**
 * Genera el markup HTML accesible para la paginación con estilos UNEFA.
 */
if (!function_exists('render_paginacion')) {
    function render_paginacion(int $total, int $pagina_actual, int $por_pagina = 15, string $param_nombre = 'page'): string {
        $total_paginas = max(1, (int) ceil($total / max(1, $por_pagina)));
        if ($total_paginas <= 1) {
            return '';
        }

        $pagina_actual = max(1, min($pagina_actual, $total_paginas));
        $desde = (($pagina_actual - 1) * $por_pagina) + 1;
        $hasta = min($total, $pagina_actual * $por_pagina);

        $query_params = $_GET;

        $construir_url = function (int $pag) use ($query_params, $param_nombre): string {
            $query_params[$param_nombre] = $pag;
            return '?' . http_build_query($query_params);
        };

        $html = '<div class="pagination-container">';
        $html .= '<div class="pagination-info">Mostrando <strong>' . $desde . '</strong>–<strong>' . $hasta . '</strong> de <strong>' . $total . '</strong> registros</div>';
        $html .= '<ul class="pagination-nav">';

        // Botón Anterior
        if ($pagina_actual > 1) {
            $html .= '<li><a class="pagination-link" href="' . h($construir_url($pagina_actual - 1)) . '" aria-label="Anterior">&laquo;</a></li>';
        } else {
            $html .= '<li><span class="pagination-link disabled" aria-disabled="true">&laquo;</span></li>';
        }

        // Rango de páginas (máximo 5 páginas visibles)
        $inicio = max(1, $pagina_actual - 2);
        $fin = min($total_paginas, $pagina_actual + 2);

        if ($inicio > 1) {
            $html .= '<li><a class="pagination-link" href="' . h($construir_url(1)) . '">1</a></li>';
            if ($inicio > 2) {
                $html .= '<li><span class="pagination-link disabled">&hellip;</span></li>';
            }
        }

        for ($i = $inicio; $i <= $fin; $i++) {
            if ($i === $pagina_actual) {
                $html .= '<li><span class="pagination-link active" aria-current="page">' . $i . '</span></li>';
            } else {
                $html .= '<li><a class="pagination-link" href="' . h($construir_url($i)) . '">' . $i . '</a></li>';
            }
        }

        if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) {
                $html .= '<li><span class="pagination-link disabled">&hellip;</span></li>';
            }
            $html .= '<li><a class="pagination-link" href="' . h($construir_url($total_paginas)) . '">' . $total_paginas . '</a></li>';
        }

        // Botón Siguiente
        if ($pagina_actual < $total_paginas) {
            $html .= '<li><a class="pagination-link" href="' . h($construir_url($pagina_actual + 1)) . '" aria-label="Siguiente">&raquo;</a></li>';
        } else {
            $html .= '<li><span class="pagination-link disabled" aria-disabled="true">&raquo;</span></li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}

/**
 * Retorna el nombre en español del día de la semana (1 = Lunes, 7 = Domingo).
 */
if (!function_exists('nombre_dia_semana')) {
    function nombre_dia_semana(int $dia): string {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
        return $dias[$dia] ?? 'Día ' . $dia;
    }
}

/**
 * Formatea un rango horario en formato legible de 12 horas con AM/PM.
 */
if (!function_exists('formatear_rango_horario')) {
    function formatear_rango_horario(string $horaInicio, string $horaFin): string {
        $ini = date('h:i A', strtotime($horaInicio));
        $fin = date('h:i A', strtotime($horaFin));
        return $ini . ' - ' . $fin;
    }
}

/**
 * Retorna el nombre legible del rol según su ID numérico.
 */
if (!function_exists('obtener_nombre_rol')) {
    function obtener_nombre_rol($rol_id): string {
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

/**
 * Redirige al dashboard correspondiente según el rol del usuario.
 */
if (!function_exists('redirigir_por_rol')) {
    function redirigir_por_rol($rol_id): void {
        $base = obtener_ruta_base();
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
