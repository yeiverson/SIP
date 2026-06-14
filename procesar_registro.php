<?php

require_once 'config.php';
require_once 'procesar.php';

header('Content-Type: application/json; charset=utf-8');

$lista_errores = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$datos = procesar_datos_desde_post();

$documento_valido = function ($valor, $todos_los_datos) {
    $tipo = (string) ($todos_los_datos['tipoDocumento'] ?? '');
    $valor = (string) $valor;
    if ($tipo === 'P') {
        return preg_match('/^[A-Za-z0-9]{4,20}$/', $valor);
    }
    return $cedula_valida($valor);
};

$errores = [
    'tipoDocumento' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona una opción de tipo de documento',
    ],
    'cedula' => [
        'validar' => $documento_valido,
        'mensaje' => 'Número de documento incorrecto. V/E: 7-8 dígitos, P: 4-20 alfanumérico',
    ],
    'email' => [
        'validar' => $email_valido,
        'mensaje' => 'Error en el correo electrónico',
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
        $tipo = (string) $datos['tipoDocumento'];
        $doc_raw = (string) $datos['cedula'];

        if ($tipo === 'P') {
            $cedula_db = 0;
            $numero_doc_db = strtoupper($doc_raw);
        } else {
            $cedula_db = (int) preg_replace('/\D/', '', $doc_raw);
            $numero_doc_db = (string) $cedula_db;
        }

        $sql = 'INSERT INTO usuarios (cedula, tipo_cedula, numero_documento, nombres, apellidos, email, password, telefono, direccion, rol_id, estatus) 
                VALUES (:ci, :tipo, :ndoc, :nom, :ape, :mail, :pass, :tel, :dir, 5, :estatus)';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':ci'      => $cedula_db,
            ':tipo'    => $tipo,
            ':ndoc'    => $numero_doc_db,
            ':nom'     => 'Pendiente',
            ':ape'     => 'Pendiente',
            ':mail'    => (string) $datos['email'],
            ':pass'    => password_hash((string) $datos['password'], PASSWORD_BCRYPT),
            ':tel'     => null,
            ':dir'     => 'Pendiente',
            ':estatus' => 'Activo',
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Usuario registrado']);
        exit;
    } catch (PDOException $e) {
        if ((string) $e->getCode() === '23505') {
            $mensaje_sql = $e->getMessage();
            if (stripos($mensaje_sql, 'telefono') !== false) {
                $lista_errores['telefono'] = 'Error: Este número de teléfono ya pertenece a un usuario registrado.';
            } elseif (stripos($mensaje_sql, 'email') !== false || stripos($mensaje_sql, 'correo') !== false) {
                $lista_errores['email'] = 'Error: Este correo electrónico ya está registrado en el sistema.';
            } elseif (stripos($mensaje_sql, 'cedula') !== false) {
                $lista_errores['cedula'] = 'Error: Esta cédula ya pertenece a un usuario registrado.';
            } elseif (stripos($mensaje_sql, 'numero_documento') !== false) {
                $lista_errores['cedula'] = 'Error: Este número de documento ya está registrado en el sistema.';
            } else {
                $lista_errores['db'] = 'Uno de los datos ingresados ya se encuentra registrado. Verifica cédula y correo.';
            }
        } else {
            error_log('Error de registro: ' . $e->getMessage());
            $lista_errores['db'] = 'Hubo un problema de conexión con el servidor de la universidad.';
        }
    }
}

$mensaje = !empty($lista_errores) ? implode(' ', array_values($lista_errores)) : 'Error de validación';
echo json_encode(['status' => 'error', 'message' => $mensaje, 'errors' => $lista_errores]);
