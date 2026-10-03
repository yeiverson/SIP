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
        $stmt = $pdo->query('SELECT * FROM baremo_preguntas ORDER BY categoria, orden');
        return $stmt->fetchAll();
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
