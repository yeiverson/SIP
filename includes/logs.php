<?php
/**
 * Sistema de auditoría (logs inmutables).
 */

function registrar_log($pdo, $accion, $entidad = null, $entidad_id = null, $detalle = null) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        
        $stmt = $pdo->prepare(
            'INSERT INTO logs_auditoria (usuario_id, accion, entidad, entidad_id, detalle, direccion_ip)
             VALUES (:uid, :accion, :entidad, :eid, :detalle, :ip)'
        );
        $stmt->execute([
            ':uid'     => $usuario_id,
            ':accion'  => $accion,
            ':entidad' => $entidad,
            ':eid'     => $entidad_id,
            ':detalle' => $detalle,
            ':ip'      => $ip,
        ]);
    } catch (PDOException $e) {
        error_log("Error al registrar log: " . $e->getMessage());
    }
}
