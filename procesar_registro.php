<?php
/**
 * Procesamiento de registro de nuevos aspirantes.
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'procesar.php';

iniciar_sesion_segura();

header('Content-Type: application/json; charset=utf-8');

$lista_errores = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_respuesta(['status' => 'error', 'message' => 'Método no permitido'], 405);
}

// Validar CSRF si viene token en POST o headers
$csrf_token_recibido = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if ($csrf_token_recibido && !validar_csrf($csrf_token_recibido)) {
    json_respuesta(['status' => 'error', 'message' => 'Token de seguridad inválido o expirado. Recargue la página.'], 403);
}

$datos = procesar_datos_desde_post();

$documento_valido = function ($valor, $todos_los_datos) use ($cedula_valida) {
    $tipo = (string) ($todos_los_datos['tipoDocumento'] ?? 'V');
    $valor = (string) $valor;
    if ($tipo === 'P') {
        return (bool) preg_match('/^[A-Za-z0-9]{4,20}$/', $valor);
    }
    return $cedula_valida($valor);
};

$errores = [
    'tipoDocumento' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el tipo de documento',
    ],
    'cedula' => [
        'validar' => $documento_valido,
        'mensaje' => 'Número de documento incorrecto (V/E: 6-8 dígitos numéricos, P: 4-20 alfanumérico)',
    ],
    'email' => [
        'validar' => $email_valido,
        'mensaje' => 'Correo electrónico no válido',
    ],
    'password' => [
        'validar' => $password,
        'mensaje' => 'La contraseña debe tener al menos 8 caracteres y una mayúscula',
    ],
    'confirm_password' => [
        'validar' => function ($valor, $todos_los_datos) use ($password) {
            $valor = (string) $valor;
            $validacion_formato = $password($valor);
            $original = isset($todos_los_datos['password'])
                ? (string) $todos_los_datos['password']
                : '';

            return $validacion_formato && hash_equals($original, $valor);
        },
        'mensaje' => 'Las claves no coinciden. Por favor ingresa la clave nuevamente',
    ],
];

foreach ($errores as $campo => $array_interno) {
    $recibir = $datos[$campo] ?? '';

    if (!$array_interno['validar']($recibir, $datos)) {
        $lista_errores[$campo] = $array_interno['mensaje'];
    }
}

if (empty($lista_errores)) {
    try {
        $tipo = strtoupper(trim((string) ($datos['tipoDocumento'] ?? 'V')));
        $doc_raw = trim((string) ($datos['cedula'] ?? ''));

        if ($tipo === 'P') {
            $cedula_db = 0;
            $numero_doc_db = strtoupper($doc_raw);
        } else {
            $cedula_db = (int) solo_numeros($doc_raw);
            $numero_doc_db = (string) $cedula_db;
        }

        $sql = 'INSERT INTO usuarios (cedula, tipo_cedula, numero_documento, nombres, apellidos, email, password, telefono, direccion, rol_id, estatus, estado_aspirante) 
                VALUES (:ci, :tipo, :ndoc, :nom, :ape, :mail, :pass, :tel, :dir, 5, :estatus, :estado_asp)';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':ci'         => $cedula_db,
            ':tipo'       => $tipo,
            ':ndoc'       => $numero_doc_db,
            ':nom'        => 'Pendiente',
            ':ape'        => 'Pendiente',
            ':mail'       => strtolower(trim((string) $datos['email'])),
            ':pass'       => password_hash((string) $datos['password'], PASSWORD_DEFAULT),
            ':tel'        => null,
            ':dir'        => 'Pendiente',
            ':estatus'    => 'Activo',
            ':estado_asp' => 'En Revision Digital',
        ]);

        json_respuesta(['status' => 'success', 'message' => '¡Registro exitoso! Ya puedes iniciar sesión con tus credenciales.']);
    } catch (PDOException $e) {
        if ((string) $e->getCode() === '23505') {
            $mensaje_sql = $e->getMessage();
            if (stripos($mensaje_sql, 'telefono') !== false) {
                $lista_errores['telefono'] = 'Este número de teléfono ya se encuentra registrado.';
            } elseif (stripos($mensaje_sql, 'email') !== false || stripos($mensaje_sql, 'correo') !== false) {
                $lista_errores['email'] = 'Este correo electrónico ya está registrado en el sistema.';
            } elseif (stripos($mensaje_sql, 'cedula') !== false || stripos($mensaje_sql, 'numero_documento') !== false) {
                $lista_errores['cedula'] = 'Este número de documento de identidad ya está registrado.';
            } else {
                $lista_errores['db'] = 'Uno de los datos ingresados ya se encuentra registrado en el sistema.';
            }
        } else {
            error_log('[SIP] Error de registro: ' . $e->getMessage());
            $lista_errores['db'] = 'Hubo un error al procesar el registro en la base de datos.';
        }
    }
}

$mensaje = !empty($lista_errores) ? implode(' ', array_values($lista_errores)) : 'Error de validación';
json_respuesta(['status' => 'error', 'message' => $mensaje, 'errors' => $lista_errores], 422);
