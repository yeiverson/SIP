<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(3);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$docente_id = $_SESSION['usuario_id'];
$nombre_docente = $_SESSION['nombre_full'];
$mensaje = '';

// Procesar guardado de notas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_borrador'])) {
        $seccion_id = (int)$_POST['seccion_id'];
        try {
            $pdo->beginTransaction();
            foreach ($_POST['nota'] as $uid => $nota) {
                $uid = (int)$uid;
                $nota_val = $nota !== '' ? (int)$nota : null;
                $inasistencia = isset($_POST['inasistencia'][$uid]) ? true : false;

                if ($nota_val !== null && ($nota_val < 0 || $nota_val > 20)) continue;

                $stmt = $pdo->prepare("INSERT INTO actas_notas (seccion_id, usuario_id, nota, inasistencia, estatus)
                        VALUES (:sec, :uid, :nota, :inas, 'Borrador')
                        ON CONFLICT (seccion_id, usuario_id) DO UPDATE
                        SET nota = :nota2, inasistencia = :inas2, updated_at = NOW()");
                $stmt->execute([
                    ':sec' => $seccion_id, ':uid' => $uid,
                    ':nota' => $nota_val, ':inas' => $inasistencia,
                    ':nota2' => $nota_val, ':inas2' => $inasistencia,
                ]);
            }
            $pdo->commit();
            registrar_log($pdo, 'Guardar borrador notas', 'actas_notas', $seccion_id);
            $mensaje = alerta_success('Borrador guardado exitosamente.');
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = alerta_error('Error al guardar: ' . $e->getMessage());
        }
    }
    elseif (isset($_POST['cerrar_acta'])) {
        $seccion_id = (int)$_POST['seccion_id'];
        try {
            $stmt = $pdo->prepare("UPDATE actas_notas SET estatus='Definitiva', updated_at=NOW()
                    WHERE seccion_id = :sec AND estatus = 'Borrador'");
            $stmt->execute([':sec' => $seccion_id]);
            registrar_log($pdo, 'Cierre definitivo de acta', 'actas_notas', $seccion_id);
            $mensaje = alerta_success('Acta cerrada definitivamente. Las notas han sido publicadas.');
        } catch (PDOException $e) {
            $mensaje = alerta_error('Error al cerrar acta.');
        }
    }
}

// Obtener secciones del docente
$secciones = $pdo->prepare("SELECT sec.*, asig.nombre as materia_nombre, asig.codigo as materia_codigo, asig.uc,
                            pl.nombre as plan_nombre, s.nombre as sede_nombre,
                            (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = sec.id AND i.estatus = 'Formalizada') as inscritos
                            FROM secciones sec
                            JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                            JOIN plan_estudios pl ON pl.id = sec.plan_id
                            JOIN sedes s ON s.id = sec.sede_id
                            WHERE sec.profesor_id = :prof AND sec.activa = true
                            ORDER BY pl.nombre, asig.nombre");
$secciones->execute([':prof' => $docente_id]);
$secciones = $secciones->fetchAll();

$titulo = 'Panel Docente';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">UNEFA</span>
            </div>
            <p>Docente: <?php echo h($nombre_docente); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active">📅 Mi Carga Académica</a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Mi Carga Académica</h2>
            <p><?php echo fecha_hoy_formateada(); ?></p>
        </header>

        <?php echo $mensaje; ?>

        <?php if (count($secciones) === 0): ?>
            <div class="alert alert-info">No tiene secciones asignadas para este período.</div>
        <?php else: ?>
            <?php foreach ($secciones as $sec): ?>
            <div class="form-section" style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                    <div>
                        <h3><?php echo h($sec['materia_nombre']); ?> (<?php echo h($sec['materia_codigo']); ?>)</h3>
                        <p style="color:#666;">
                            <?php echo h($sec['plan_nombre']); ?> &middot;
                            Sede <?php echo h($sec['sede_nombre']); ?> &middot;
                            Sección <?php echo h($sec['seccion']); ?> &middot;
                            <?php echo $sec['uc']; ?> UC &middot;
                            <?php echo $sec['inscritos']; ?>/<?php echo $sec['cupo_maximo']; ?> inscritos
                        </p>
                    </div>
                    <div>
                        <?php
                        $acta_status = $pdo->prepare("SELECT DISTINCT estatus FROM actas_notas WHERE seccion_id = :sid");
                        $acta_status->execute([':sid' => $sec['id']]);
                        $statuses = $acta_status->fetchAll(PDO::FETCH_COLUMN);
                        $definitiva = in_array('Definitiva', $statuses);
                        ?>
                        <span class="badge <?php echo $definitiva ? 'badge-inscripcion' : 'badge-planar'; ?>">
                            <?php echo $definitiva ? 'Acta Cerrada' : 'Borrador'; ?>
                        </span>
                    </div>
                </div>

                <?php if (!$definitiva): ?>
                <form method="POST" style="margin-top:15px;">
                    <input type="hidden" name="seccion_id" value="<?php echo $sec['id']; ?>">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Estudiante</th>
                                <th style="width:80px;">Nota (0-20)</th>
                                <th style="width:50px;">N/S</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $estudiantes = $pdo->prepare("SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos,
                                                          an.nota, an.inasistencia, an.estatus as acta_estatus
                                                          FROM inscripciones i
                                                          JOIN usuarios u ON u.id = i.usuario_id
                                                          LEFT JOIN actas_notas an ON an.seccion_id = i.seccion_id AND an.usuario_id = u.id
                                                          WHERE i.seccion_id = :sec AND i.estatus = 'Formalizada'
                                                          ORDER BY u.apellidos, u.nombres");
                            $estudiantes->execute([':sec' => $sec['id']]);
                            $alumnos = $estudiantes->fetchAll();
                            ?>
                            <?php if (count($alumnos) === 0): ?>
                            <tr><td colspan="5" class="text-center">No hay estudiantes formalizados en esta sección.</td></tr>
                            <?php else: ?>
                            <?php foreach ($alumnos as $alu): ?>
                            <tr>
                                <td><?php echo h($alu['tipo_cedula'] . '-' . $alu['numero_documento']); ?></td>
                                <td><?php echo h($alu['nombres'] . ' ' . $alu['apellidos']); ?></td>
                                <td>
                                    <input type="number" name="nota[<?php echo $alu['id']; ?>]"
                                           value="<?php echo $alu['nota'] !== null ? $alu['nota'] : ''; ?>"
                                           min="0" max="20" style="width:70px;text-align:center;"
                                           <?php echo $definitiva ? 'readonly' : ''; ?>>
                                </td>
                                <td style="text-align:center;">
                                    <input type="checkbox" name="inasistencia[<?php echo $alu['id']; ?>]"
                                           <?php echo $alu['inasistencia'] ? 'checked' : ''; ?>
                                           <?php echo $definitiva ? 'disabled' : ''; ?>>
                                </td>
                                <td>
                                    <?php if ($alu['nota'] !== null): ?>
                                        <span class="badge <?php echo $alu['nota'] >= 14 ? 'badge-inscripcion' : 'badge-planar'; ?>">
                                            <?php echo $alu['nota'] >= 14 ? 'Aprobado' : 'Reprobado'; ?>
                                        </span>
                                    <?php elseif ($alu['inasistencia']): ?>
                                        <span class="badge badge-planar">Inasistente</span>
                                    <?php else: ?>
                                        <span style="color:#999;">Sin nota</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div style="margin-top:10px;display:flex;gap:10px;">
                        <button type="submit" name="guardar_borrador" class="btn-action btn-green">💾 Guardar Borrador</button>
                        <button type="submit" name="cerrar_acta" class="btn-action btn-orange"
                                onclick="return confirm('¿Está seguro de cerrar el acta definitivamente? Esta acción no se puede deshacer.')">
                            🔒 Cierre Definitivo de Acta
                        </button>
                    </div>
                </form>
                <?php else: ?>
                    <!-- Acta cerrada: solo lectura -->
                    <table class="data-table" style="margin-top:15px;">
                        <thead>
                            <tr><th>Documento</th><th>Estudiante</th><th>Nota</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $estudiantes = $pdo->prepare("SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos,
                                                          an.nota, an.inasistencia
                                                          FROM inscripciones i
                                                          JOIN usuarios u ON u.id = i.usuario_id
                                                          LEFT JOIN actas_notas an ON an.seccion_id = i.seccion_id AND an.usuario_id = u.id
                                                          WHERE i.seccion_id = :sec AND i.estatus = 'Formalizada'
                                                          ORDER BY u.apellidos, u.nombres");
                            $estudiantes->execute([':sec' => $sec['id']]);
                            while ($alu = $estudiantes->fetch()): ?>
                            <tr>
                                <td><?php echo h($alu['tipo_cedula'] . '-' . $alu['numero_documento']); ?></td>
                                <td><?php echo h($alu['nombres'] . ' ' . $alu['apellidos']); ?></td>
                                <td><strong><?php echo $alu['inasistencia'] ? 'N/S' : ($alu['nota'] ?? '—'); ?></strong></td>
                                <td>
                                    <?php if ($alu['inasistencia']): ?>
                                        <span class="badge badge-planar">Inasistente</span>
                                    <?php elseif ($alu['nota'] !== null): ?>
                                        <span class="badge <?php echo $alu['nota'] >= 14 ? 'badge-inscripcion' : 'badge-planar'; ?>">
                                            <?php echo $alu['nota'] >= 14 ? 'Aprobado' : 'Reprobado'; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
