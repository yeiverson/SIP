<?php
/**
 * Validadores y utilidades compartidas entre procesar_registro.php y procesar_perfil.php.
 * Mismo estilo que tenías: $vacio, $password, etc.
 */

if (!function_exists('procesar_datos_desde_post')) {
    function procesar_datos_desde_post(): array
    {
        $datos = [];
        foreach ($_POST as $clave => $valor) {
            $datos[$clave] = is_string($valor) ? trim($valor) : $valor;
        }
        return $datos;
    }
}

$vacio = function ($v) {
    return $v !== null && $v !== '' && trim((string) $v) !== '';
};

$password = function ($valor) {
    $valor = (string) $valor;
    return $valor !== ''
        && mb_strlen($valor) >= 8
        && preg_match('/\p{Lu}/u', $valor);
};

$email_valido = function ($valor) {
    return (bool) filter_var(trim((string) $valor), FILTER_VALIDATE_EMAIL);
};

$cedula_valida = function ($valor) {
    $solo_numeros = preg_replace('/\D/', '', (string) $valor);
    return ctype_digit($solo_numeros)
        && strlen($solo_numeros) >= 7
        && strlen($solo_numeros) <= 8;
};

$telefono_ve = function ($valor) {
    $valor_limpio = preg_replace('/\D/', '', (string) $valor);
    return strlen($valor_limpio) === 11 && substr($valor_limpio, 0, 1) === '0';
};
