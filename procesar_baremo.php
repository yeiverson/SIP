<?php
session_start();
require_once 'config.php';

// Si no hay sesión iniciada, pa' fuera
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['usuario_id'] ?? $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Aquí puedes procesar y guardar la dirección, datos académicos (update a usuarios u otra tabla)
    // Ejemplo rápido para actualizar info de usuario (opcional):
    // $primer_nombre = $_POST['primer_nombre'] ?? '';
    // etc...

    try {
        // Iniciar transacción por si algo falla, no se guarde por la mitad
        $pdo->beginTransaction();

        // Eliminar respuestas anteriores de este aspirante si las tuviera (opcional, si permiten re-enviar)
        $stmtDelete = $pdo->prepare("DELETE FROM respuestas_baremo WHERE id_aspirante = :aspirante");
        $stmtDelete->execute([':aspirante' => $userId]);

        // Guardar las nuevas respuestas
        // Fíjate que en el HTML el radio button se llama name="baremo[ID_PREGUNTA]"
        if (isset($_POST['baremo']) && is_array($_POST['baremo'])) {
            $stmtInsert = $pdo->prepare("INSERT INTO respuestas_baremo (id_aspirante, id_pregunta, respuesta) VALUES (:aspirante, :pregunta, :respuesta)");
            
            foreach ($_POST['baremo'] as $id_pregunta => $respuesta) {
                // $respuesta vendrá como 'si' o 'no'
                $stmtInsert->execute([
                    ':aspirante' => $userId,
                    ':pregunta'  => $id_pregunta,
                    ':respuesta' => $respuesta
                ]);
            }
        }

        // Si subiste archivos, aquí puedes procesar la subida con move_uploaded_file() 
        // ...

        $pdo->commit();

        // Redirigir al dashboard con un mensaje de éxito
        header('Location: dashboard.php?msg=baremo_exito');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error al guardar el baremo: " . $e->getMessage());
        die("Ocurrió un error al guardar el baremo.");
    }
} else {
    header('Location: dashboard.php');
}