<?php
/**
 * Rate Limiter — SIP-Postgrado UNEFA
 *
 * Controla la cantidad de peticiones por IP en una ventana de tiempo.
 * Almacena contadores en archivos temporales (sin Redis ni Memcached).
 *
 * Uso típico en una API:
 *   RateLimiter::check('api', 60, 60);  // 60 req por 60 segundos
 */

class RateLimiter
{
    private const STORAGE_DIR = __DIR__ . '/../uploads/rate/';

    /**
     * Verifica si la IP actual ha superado el límite.
     * Si lo supera, emite la cabecera 429 y termina la ejecución.
     *
     * @param string $bucket    Identificador del límite (p.e. 'api', 'login')
     * @param int    $limite    Número máximo de peticiones permitidas
     * @param int    $ventana   Ventana de tiempo en segundos
     */
    public static function check(string $bucket = 'default', int $limite = 60, int $ventana = 60): void
    {
        $ip   = self::normalizarIp($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $file = self::STORAGE_DIR . $bucket . '_' . md5($ip) . '.json';

        $ahora = time();
        $datos = ['hits' => [], 'ip' => $ip];

        if (is_file($file)) {
            $raw = file_get_contents($file);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $datos = $decoded;
                }
            }
        }

        // Filtrar hits fuera de la ventana de tiempo
        $datos['hits'] = array_values(array_filter(
            $datos['hits'] ?? [],
            fn(int $t) => ($ahora - $t) < $ventana
        ));

        // Agregar el hit actual
        $datos['hits'][] = $ahora;

        // Persistir antes de responder
        @file_put_contents($file, json_encode($datos), LOCK_EX);

        // Limpiar archivos viejos de vez en cuando (1% de probabilidad)
        if (random_int(1, 100) === 1) {
            self::limpiarArchivosViejos($ventana * 2);
        }

        $conteo = count($datos['hits']);

        // Cabeceras informativas
        header("X-RateLimit-Limit: {$limite}");
        header("X-RateLimit-Remaining: " . max(0, $limite - $conteo));
        header("X-RateLimit-Reset: " . ($ahora + $ventana));

        if ($conteo > $limite) {
            header("Retry-After: {$ventana}");
            http_response_code(429);
            echo json_encode([
                'error'       => 'Demasiadas solicitudes. Por favor espere antes de intentarlo de nuevo.',
                'retry_after' => $ventana,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // ----------------------------------------------------------------
    //  Utilidades internas
    // ----------------------------------------------------------------

    private static function normalizarIp(string $ip): string
    {
        // Soporte para proxies con X-Forwarded-For
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded) {
            $partes = explode(',', $forwarded);
            $ip = trim($partes[0]);
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    private static function limpiarArchivosViejos(int $ttlSegundos): void
    {
        $dir = self::STORAGE_DIR;
        if (!is_dir($dir)) {
            return;
        }
        $umbral = time() - $ttlSegundos;
        foreach (glob($dir . '*.json') ?: [] as $file) {
            if (filemtime($file) < $umbral) {
                @unlink($file);
            }
        }
    }
}
