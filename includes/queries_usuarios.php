<?php
/**
 * Consultas a la tabla usuarios (registro y perfil).
 * - llenado_de_perfil.php y procesar_perfil.php usan query_usuario_por_id y query_actualizar_perfil_usuario.
 * - La validación y armado de datos sigue en procesar_registro.php y procesar_perfil.php.
 */

if (!function_exists('query_usuario_por_id')) {
    /**
     * @param PDO   $pdo
     * @param int   $id
     * @return array|false fila asociativa o false si no existe
     */
    function query_usuario_por_id($pdo, $id)
    {
        $stmt = $pdo->prepare('SELECT cedula, tipo_cedula, email FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? $fila : false;
    }
}

if (!function_exists('query_insertar_usuario_registro')) {
    /**
     * Inserta un usuario nuevo (mismos campos que el INSERT original en procesar_registro.php).
     *
     * @param PDO   $pdo
     * @param array $params claves: cedula_limpia, tipo, email, password_hash
     * @return bool true si se ejecutó sin excepción (el caller captura PDOException)
     */
    function query_insertar_usuario_registro($pdo, $params)
    {
        $sql = 'INSERT INTO usuarios (cedula, tipo_cedula, nombres, apellidos, email, password, telefono, direccion) 
                VALUES (:ci, :tipo, :nom, :ape, :mail, :pass, :tel, :dir)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ci'   => $params['cedula_limpia'],
            ':tipo' => $params['tipo'],
            ':nom'  => 'Pendiente',
            ':ape'  => 'Pendiente',
            ':mail' => $params['email'],
            ':pass' => $params['password_hash'],
            ':tel'  => null,
            ':dir'  => 'Pendiente',
        ]);
        return true;
    }
}

if (!function_exists('query_actualizar_perfil_usuario')) {
    /**
     * UPDATE de nombres, apellidos, teléfono (celular) y dirección tras completar el perfil.
     *
     * @param PDO   $pdo
     * @param int   $userId
     * @param array $params claves: nombres, apellidos, telefono, direccion
     */
    function query_actualizar_perfil_usuario($pdo, $userId, $params)
    {
        $sql = 'UPDATE usuarios SET
                    nombres = :nom,
                    apellidos = :ape,
                    telefono = :tel,
                    direccion = :dir
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $params['nombres'],
            ':ape' => $params['apellidos'],
            ':tel' => $params['telefono'],
            ':dir' => $params['direccion'],
            ':id'  => $userId,
        ]);
    }
}
