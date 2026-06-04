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

$errores = [
    'tipoDocumento' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona una opción de tipo de documento',
    ],
    'cedula' => [
        'validar' => $cedula_valida,
        'mensaje' => 'Cédula incorrecta',
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
        $cedula_limpia = preg_replace('/\D/', '', (string) $datos['cedula']);
        $tipo = (string) $datos['tipoDocumento'];

        $sql = 'INSERT INTO usuarios (cedula, tipo_cedula, nombres, apellidos, email, password, telefono, direccion) 
                VALUES (:ci, :tipo, :nom, :ape, :mail, :pass, :tel, :dir)';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':ci'   => $cedula_limpia,
            ':tipo' => $tipo,
            ':nom'  => 'Pendiente',
            ':ape'  => 'Pendiente',
            ':mail' => (string) $datos['email'],
            ':pass' => password_hash((string) $datos['password'], PASSWORD_BCRYPT),
            ':tel'  => null,
            ':dir'  => 'Pendiente',
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
