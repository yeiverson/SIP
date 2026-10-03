<?php
/**
 * Modelo de Baremo — SIP-Postgrado UNEFA
 * Gestión de preguntas y respuestas del baremo digital de evaluación docente / aspirante.
 */

class Baremo
{
    /**
     * Lista todas las preguntas del baremo ordenadas por categoría y orden.
     */
    public static function listarPreguntas(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT * FROM baremo_preguntas ORDER BY categoria, orden, id');
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una pregunta específica por su ID.
     */
    public static function obtenerPregunta(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM baremo_preguntas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crea una nueva pregunta para el baremo y retorna su ID generado.
     */
    public static function crearPregunta(PDO $pdo, string $pregunta, string $categoria, int $orden): int
    {
        $sql = 'INSERT INTO baremo_preguntas (pregunta, categoria, orden)
                VALUES (:p, :c, :o)
                RETURNING id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':p' => trim($pregunta),
            ':c' => trim($categoria),
            ':o' => $orden,
        ]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Actualiza una pregunta existente en el baremo.
     */
    public static function actualizarPregunta(PDO $pdo, int $id, string $pregunta, string $categoria, int $orden): bool
    {
        $sql = 'UPDATE baremo_preguntas 
                SET pregunta = :p, categoria = :c, orden = :o 
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':p'  => trim($pregunta),
            ':c'  => trim($categoria),
            ':o'  => $orden,
            ':id' => $id,
        ]);
    }

    /**
     * Elimina una pregunta del baremo y sus respuestas asociadas si existen.
     */
    public static function eliminarPregunta(PDO $pdo, int $id): bool
    {
        try {
            $pdo->beginTransaction();
            $stmtResp = $pdo->prepare('DELETE FROM respuestas_baremo WHERE id_pregunta = :id');
            $stmtResp->execute([':id' => $id]);

            $stmtPreg = $pdo->prepare('DELETE FROM baremo_preguntas WHERE id = :id');
            $stmtPreg->execute([':id' => $id]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Guarda las respuestas de un aspirante/docente reemplazando las anteriores.
     */
    public static function guardarRespuestas(PDO $pdo, int $aspiranteId, array $respuestas): bool
    {
        $pdo->beginTransaction();
        try {
            $stmtDel = $pdo->prepare('DELETE FROM respuestas_baremo WHERE id_aspirante = :aspirante');
            $stmtDel->execute([':aspirante' => $aspiranteId]);

            $stmtIns = $pdo->prepare('INSERT INTO respuestas_baremo (id_aspirante, id_pregunta, respuesta)
                                     VALUES (:aspirante, :pregunta, :respuesta)');

            foreach ($respuestas as $preguntaId => $respuesta) {
                $stmtIns->execute([
                    ':aspirante' => $aspiranteId,
                    ':pregunta'  => (int) $preguntaId,
                    ':respuesta' => trim($respuesta),
                ]);
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Obtiene las respuestas registradas para un aspirante dado.
     */
    public static function obtenerRespuestasAspirante(PDO $pdo, int $aspiranteId): array
    {
        $sql = 'SELECT rb.*, bp.pregunta, bp.categoria
                FROM respuestas_baremo rb
                JOIN baremo_preguntas bp ON bp.id = rb.id_pregunta
                WHERE rb.id_aspirante = :id
                ORDER BY bp.categoria, bp.orden';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $aspiranteId]);
        return $stmt->fetchAll();
    }
}
