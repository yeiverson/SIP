<?php
/**
 * Logger estructurado — SIP-Postgrado UNEFA
 *
 * Escribe entradas en logs/app.log con niveles INFO, WARNING y ERROR.
 * Complementa el sistema de auditoría en BD para errores previos a la conexión.
 *
 * Formato: [2026-10-02 19:00:00] [ERROR] [ip:x.x.x.x|uid:5] mensaje {"clave":"valor"}
 */

class Logger
{
    private const LOG_FILE   = __DIR__ . '/../logs/app.log';
    private const MAX_BYTES  = 5 * 1024 * 1024; // 5 MB — rota automáticamente

    // ----------------------------------------------------------------
    //  Métodos públicos de nivel
    // ----------------------------------------------------------------

    public static function info(string $mensaje, array $contexto = []): void
    {
        self::escribir('INFO', $mensaje, $contexto);
    }

    public static function warning(string $mensaje, array $contexto = []): void
    {
        self::escribir('WARNING', $mensaje, $contexto);
    }

    public static function error(string $mensaje, array $contexto = []): void
    {
        self::escribir('ERROR', $mensaje, $contexto);
    }

    /**
     * Atajo para registrar una excepción con traza abreviada.
     */
    public static function exception(string $mensaje, Throwable $e, array $extra = []): void
    {
        self::escribir('ERROR', $mensaje, array_merge([
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => basename($e->getFile()) . ':' . $e->getLine(),
        ], $extra));
    }

    // ----------------------------------------------------------------
    //  Motor interno
    // ----------------------------------------------------------------

    private static function escribir(string $nivel, string $mensaje, array $contexto): void
    {
        try {
            $logFile = self::LOG_FILE;

            // Rotación: si supera el tamaño máximo, renombra y crea nuevo
            if (is_file($logFile) && filesize($logFile) > self::MAX_BYTES) {
                rename($logFile, $logFile . '.' . date('Ymd_His') . '.bak');
            }

            $ts  = date('Y-m-d H:i:s');
            $ip  = $_SERVER['REMOTE_ADDR'] ?? 'cli';
            $uid = (session_status() === PHP_SESSION_ACTIVE) ? ($_SESSION['usuario_id'] ?? '—') : '—';

            $ctx = empty($contexto) ? '' : ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $linea = "[{$ts}] [{$nivel}] [ip:{$ip}|uid:{$uid}] {$mensaje}{$ctx}" . PHP_EOL;

            file_put_contents($logFile, $linea, FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            // El logger nunca debe romper la aplicación
            error_log('[SIP Logger] Fallo al escribir log: ' . $e->getMessage());
        }
    }
}
