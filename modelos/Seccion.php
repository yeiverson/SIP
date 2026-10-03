<?php
/**
 * Modelo de Sección — SIP-Postgrado UNEFA
 * Manejo de secciones académicas, cupos, docentes asignados y horarios.
 */

class Seccion
{
    /**
     * Cuenta el total de secciones activas o generales.
     */
    public static function contar(PDO $pdo, bool $soloActivas = true): int
    {
        $sql = 'SELECT COUNT(*) FROM secciones' . ($soloActivas ? ' WHERE activa = TRUE' : '');
        return (int) $pdo->query($sql)->fetchColumn();
    }

    /**
     * Busca una sección por ID con detalles de asignatura y docente.
     */
    public static function buscarPorId(PDO $pdo, int $id): ?array
    {
        $sql = 'SELECT s.*, a.nombre AS asignatura_nombre,
                       u.nombres AS docente_nombres, u.apellidos AS docente_apellidos
                FROM secciones s
                JOIN asignaturas a ON a.codigo = s.asignatura_codigo
                LEFT JOIN usuarios u ON u.id = s.profesor_id
                WHERE s.id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Verifica si un docente tiene solapamiento de horario.
     */
    public static function docenteTieneConflicto(
        PDO $pdo,
        int $profesorId,
        int $diaSemana,
        string $horaInicio,
        string $horaFin
    ): bool {
        $sql = 'SELECT COUNT(*)
                FROM horarios h
                JOIN secciones s ON s.id = h.seccion_id
                WHERE s.profesor_id = :prof
                  AND h.dia_semana = :dia
                  AND h.hora_inicio < :hora_fin
                  AND h.hora_fin > :hora_inicio
                  AND s.activa = TRUE';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':prof'        => $profesorId,
            ':dia'         => $diaSemana,
            ':hora_fin'    => $horaFin,
            ':hora_inicio' => $horaInicio,
        ]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Crea una sección y su correspondiente horario en una transacción atómica.
     */
    public static function crear(PDO $pdo, array $datos): int
    {
        $pdo->beginTransaction();
        try {
            $sqlSec = 'INSERT INTO secciones (plan_id, asignatura_codigo, seccion, profesor_id, sede_id, cupo_maximo, aula, periodo)
                       VALUES (:plan, :asig, :seccion, :prof, :sede, :cupo, :aula, :periodo)
                       RETURNING id';
            $stmtSec = $pdo->prepare($sqlSec);
            $stmtSec->execute([
                ':plan'    => (int) $datos['plan_id'],
                ':asig'    => strtoupper(trim($datos['asignatura_codigo'])),
                ':seccion' => strtoupper(trim($datos['seccion'])),
                ':prof'    => !empty($datos['profesor_id']) ? (int) $datos['profesor_id'] : null,
                ':sede'    => (int) $datos['sede_id'],
                ':cupo'    => max(1, min(50, (int) ($datos['cupo_maximo'] ?? 25))),
                ':aula'    => !empty($datos['aula']) ? trim($datos['aula']) : null,
                ':periodo' => $datos['periodo'] ?? (date('Y') . '-' . (date('n') >= 6 ? 'II' : 'I')),
            ]);
            $seccionId = (int) $stmtSec->fetchColumn();

            if (!empty($datos['dia_semana']) && !empty($datos['hora_inicio']) && !empty($datos['hora_fin'])) {
                $sqlHor = 'INSERT INTO horarios (seccion_id, dia_semana, hora_inicio, hora_fin)
                           VALUES (:sid, :dia, :inicio, :fin)';
                $stmtHor = $pdo->prepare($sqlHor);
                $stmtHor->execute([
                    ':sid'    => $seccionId,
                    ':dia'    => (int) $datos['dia_semana'],
                    ':inicio' => $datos['hora_inicio'],
                    ':fin'    => $datos['hora_fin'],
                ]);
            }

            $pdo->commit();
            return $seccionId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Lista secciones paginadas filtradas por sede y/o período.
     */
    public static function listarPaginado(
        PDO $pdo,
        int $pagina = 1,
        int $porPagina = 15,
        ?int $sedeId = null,
        ?string $periodo = null
    ): array {
        $pagina = max(1, $pagina);
        $porPagina = max(1, $porPagina);
        $offset = ($pagina - 1) * $porPagina;

        $where = ['s.activa = TRUE'];
        $params = [];

        if ($sedeId !== null) {
            $where[] = 's.sede_id = :sede';
            $params[':sede'] = $sedeId;
        }

        if ($periodo !== null) {
            $where[] = 's.periodo = :periodo';
            $params[':periodo'] = $periodo;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT s.*, a.nombre AS asignatura_nombre,
                       u.nombres AS docente_nombres, u.apellidos AS docente_apellidos
                FROM secciones s
                JOIN asignaturas a ON a.codigo = s.asignatura_codigo
                LEFT JOIN usuarios u ON u.id = s.profesor_id
                $whereSql
                ORDER BY s.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
