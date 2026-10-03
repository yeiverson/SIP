<?php
/**
 * Modelo de Usuario — SIP-Postgrado UNEFA
 * Encapsula la persistencia, búsquedas y operaciones sobre la tabla 'usuarios'.
 */

class Usuario
{
    /**
     * Busca un usuario por su identificador único.
     */
    public static function buscarPorId(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT u.*, r.nombre AS rol_nombre, s.nombre AS sede_nombre
                               FROM usuarios u
                               LEFT JOIN roles r ON r.id = u.rol_id
                               LEFT JOIN sedes s ON s.id = u.sede_id
                               WHERE u.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Busca un usuario por tipo y número de documento.
     */
    public static function buscarPorDocumento(PDO $pdo, string $tipoDoc, string $numDoc): ?array
    {
        $stmt = $pdo->prepare('SELECT u.*, r.nombre AS rol_nombre
                               FROM usuarios u
                               LEFT JOIN roles r ON r.id = u.rol_id
                               WHERE u.tipo_cedula = :tipo AND u.numero_documento = :ndoc
                               LIMIT 1');
        $stmt->execute([
            ':tipo' => strtoupper(trim($tipoDoc)),
            ':ndoc' => trim($numDoc),
        ]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Cuenta el total de usuarios, opcionalmente filtrado por rol.
     */
    public static function contar(PDO $pdo, ?int $rolId = null): int
    {
        if ($rolId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE rol_id = :rol');
            $stmt->execute([':rol' => $rolId]);
        } else {
            $stmt = $pdo->query('SELECT COUNT(*) FROM usuarios');
        }
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene una lista paginada de usuarios con filtro opcional de búsqueda.
     */
    public static function listarPaginado(
        PDO $pdo,
        int $pagina = 1,
        int $porPagina = 15,
        ?string $busqueda = null,
        ?int $rolId = null
    ): array {
        $pagina = max(1, $pagina);
        $porPagina = max(1, $porPagina);
        $offset = ($pagina - 1) * $porPagina;

        $where = [];
        $params = [];

        if ($rolId !== null) {
            $where[] = 'u.rol_id = :rol';
            $params[':rol'] = $rolId;
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $where[] = '(u.nombres ILIKE :q OR u.apellidos ILIKE :q OR u.numero_documento ILIKE :q OR u.email ILIKE :q)';
            $params[':q'] = '%' . trim($busqueda) . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos, u.email,
                       u.rol_id, r.nombre AS rol_nombre, s.nombre AS sede_nombre, u.estatus, u.estado_aspirante
                FROM usuarios u
                LEFT JOIN roles r ON r.id = u.rol_id
                LEFT JOIN sedes s ON s.id = u.sede_id
                $whereSql
                ORDER BY u.id DESC
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

    /**
     * Cuenta el total de usuarios coincidentes con los filtros aplicados.
     */
    public static function contarFiltrados(PDO $pdo, ?string $busqueda = null, ?int $rolId = null): int
    {
        $where = [];
        $params = [];

        if ($rolId !== null) {
            $where[] = 'rol_id = :rol';
            $params[':rol'] = $rolId;
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $where[] = '(nombres ILIKE :q OR apellidos ILIKE :q OR numero_documento ILIKE :q OR email ILIKE :q)';
            $params[':q'] = '%' . trim($busqueda) . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) FROM usuarios $whereSql";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Actualiza el estatus de un usuario (Activo / Bloqueado / Inactivo).
     */
    public static function cambiarEstatus(PDO $pdo, int $id, string $nuevoEstatus): bool
    {
        $stmt = $pdo->prepare('UPDATE usuarios SET estatus = :estatus WHERE id = :id');
        return $stmt->execute([
            ':estatus' => $nuevoEstatus,
            ':id'      => $id,
        ]);
    }

    /**
     * Registra un nuevo usuario y retorna su ID generado.
     */
    public static function crear(PDO $pdo, array $datos): int
    {
        $tipoDoc = strtoupper(trim($datos['tipo_documento'] ?? 'V'));
        $numDoc  = trim($datos['numero_documento'] ?? '');
        $ciLimpia = preg_replace('/\D/', '', $numDoc) ?: '0';

        $sql = "INSERT INTO usuarios (tipo_cedula, numero_documento, cedula, nombres, apellidos, email, password, rol_id, sede_id, estatus, estado_aspirante)
                VALUES (:tipo, :ndoc, :ci, :nom, :ape, :mail, :pass, :rol, :sede, :estatus, :estado_asp)
                RETURNING id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tipo'       => $tipoDoc,
            ':ndoc'       => $numDoc,
            ':ci'         => $ciLimpia,
            ':nom'        => trim($datos['nombres'] ?? 'Pendiente'),
            ':ape'        => trim($datos['apellidos'] ?? 'Pendiente'),
            ':mail'       => strtolower(trim($datos['email'] ?? '')),
            ':pass'       => password_hash($datos['password'], PASSWORD_DEFAULT),
            ':rol'        => (int) ($datos['rol_id'] ?? 5),
            ':sede'       => !empty($datos['sede_id']) ? (int) $datos['sede_id'] : null,
            ':estatus'    => $datos['estatus'] ?? 'Activo',
            ':estado_asp' => $datos['estado_aspirante'] ?? 'Admitido',
        ]);

        return (int) $stmt->fetchColumn();
    }
}
