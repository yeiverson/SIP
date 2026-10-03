<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(3);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

iniciar_sesion_segura();

$docente_id = (int)$_SESSION['usuario_id'];
$nombre_docente = $_SESSION['nombre_full'];
$mensaje = '';

// Procesar guardado de notas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $mensaje = alerta_error('Token de seguridad inválido o sesión expirada.');
    } else {
        // Guardar Borrador de Notas
        if (isset($_POST['guardar_borrador'])) {
            $seccion_id = (int)$_POST['seccion_id'];
            try {
                $pdo->beginTransaction();
                foreach ($_POST['nota'] as $uid => $nota) {
                    $uid = (int)$uid;
                    $nota_val = ($nota !== '') ? (int)$nota : null;
                    $inasistencia = isset($_POST['inasistencia'][$uid]);

                    if ($nota_val !== null && ($nota_val < 0 || $nota_val > 20)) continue;

                    $stmt = $pdo->prepare("INSERT INTO actas_notas (seccion_id, usuario_id, nota, inasistencia, estatus)
                            VALUES (:sec, :uid, :nota, :inas, 'Borrador')
                            ON CONFLICT (seccion_id, usuario_id) DO UPDATE
                            SET nota = :nota2, inasistencia = :inas2, updated_at = NOW()");
                    $stmt->execute([
                        ':sec'   => $seccion_id,
                        ':uid'   => $uid,
                        ':nota'  => $nota_val,
                        ':inas'  => $inasistencia,
                        ':nota2' => $nota_val,
                        ':inas2' => $inasistencia,
                    ]);
                }
                $pdo->commit();
                registrar_log($pdo, 'Guardar borrador notas', 'actas_notas', $seccion_id, "Docente guardó borrador sección $seccion_id");
                $mensaje = alerta_success('Borrador de calificaciones guardado exitosamente.');
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = alerta_error('Error al guardar notas: ' . $e->getMessage());
            }
        }
        // Cierre Definitivo de Acta
        elseif (isset($_POST['cerrar_acta'])) {
            $seccion_id = (int)$_POST['seccion_id'];
            try {
                $stmt = $pdo->prepare("UPDATE actas_notas SET estatus='Definitiva', updated_at=NOW()
                                       WHERE seccion_id = :sec AND estatus = 'Borrador'");
                $stmt->execute([':sec' => $seccion_id]);
                $afectados = $stmt->rowCount();
                registrar_log($pdo, 'Cierre definitivo de acta', 'actas_notas', $seccion_id, "$afectados notas cerradas definitivamente");
                $mensaje = alerta_success("Acta cerrada definitivamente. Las calificaciones han sido consolidadas y publicadas para los estudiantes.");
            } catch (PDOException $e) {
                $mensaje = alerta_error('Error al cerrar acta de notas.');
            }
        }
    }
}

// Obtener secciones del docente
$stmtSecc = $pdo->prepare("SELECT sec.*, asig.nombre as materia_nombre, asig.codigo as materia_codigo, asig.uc,
                                  pl.nombre as plan_nombre, s.nombre as sede_nombre,
                                  (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = sec.id AND i.estatus = 'Formalizada') as inscritos
                           FROM secciones sec
                           JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                           JOIN plan_estudios pl ON pl.id = sec.plan_id
                           JOIN sedes s ON s.id = sec.sede_id
                           WHERE sec.profesor_id = :prof AND sec.activa = true
                           ORDER BY pl.nombre, asig.nombre");
$stmtSecc->execute([':prof' => $docente_id]);
$secciones = $stmtSecc->fetchAll();

$titulo = 'Carga Académica y Calificaciones';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">Módulo Docente</span>
            </div>
            <p>Prof. <?php echo h($nombre_docente); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active"><span>📅 Carga Académica</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div>
                <h2>Control de Asignaturas y Actas de Calificación</h2>
                <p><?php echo fecha_hoy_formateada(); ?> &middot; Período Académico Activo</p>
            </div>
            <div>
                <span class="badge badge-navy" style="font-size:0.85rem;padding:6px 14px;">👨‍🏫 EVALUACIÓN CONTINUA</span>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($secciones); ?></span>
                    <span class="stat-lbl">Secciones Asignadas</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">👥</div>
                <div class="stat-details">
                    <?php 
                    $totalInscritos = 0;
                    foreach ($secciones as $sec) { $totalInscritos += (int)$sec['inscritos']; }
                    ?>
                    <span class="stat-val"><?php echo $totalInscritos; ?></span>
                    <span class="stat-lbl">Estudiantes Formalizados</span>
                </div>
            </div>
        </div>

        <?php if (count($secciones) === 0): ?>
            <div class="alert alert-info">Actualmente no posee asignaturas vinculadas a su perfil para este ciclo académico. Si requiere una asignación, comuníquese con su Coordinador de Programa.</div>
        <?php else: ?>
            <?php foreach ($secciones as $sec): ?>
                <?php
                // Verificar si el acta está cerrada o en borrador
                $acta_status = $pdo->prepare("SELECT DISTINCT estatus FROM actas_notas WHERE seccion_id = :sid");
                $acta_status->execute([':sid' => $sec['id']]);
                $statuses = $acta_status->fetchAll(PDO::FETCH_COLUMN);
                $definitiva = in_array('Definitiva', $statuses, true);

                // Estudiantes de la sección
                $stmtAlu = $pdo->prepare("SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos,
                                                 an.nota, an.inasistencia, an.estatus as acta_estatus
                                          FROM inscripciones i
                                          JOIN usuarios u ON u.id = i.usuario_id
                                          LEFT JOIN actas_notas an ON an.seccion_id = i.seccion_id AND an.usuario_id = u.id
                                          WHERE i.seccion_id = :sec AND i.estatus = 'Formalizada'
                                          ORDER BY u.apellidos, u.nombres");
                $stmtAlu->execute([':sec' => $sec['id']]);
                $alumnos = $stmtAlu->fetchAll();
                ?>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h3><?php echo h($sec['materia_nombre']); ?> (<?php echo h($sec['materia_codigo']); ?>)</h3>
                            <span style="font-size:0.75rem;color:#64748b;">
                                <?php echo h($sec['plan_nombre']); ?> &middot; <?php echo h($sec['sede_nombre']); ?> &middot; Sección: <strong><?php echo h($sec['seccion']); ?></strong> &middot; <?php echo $sec['uc']; ?> UC &middot; Aula: <?php echo h($sec['aula'] ?: 'Virtual'); ?>
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="badge <?php echo $definitiva ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo $definitiva ? '🔒 ACTA DEFINITIVA CERRADA' : '📝 BORRADOR EN CURSO'; ?>
                            </span>
                            <a href="../../controlador/reportes.php?tipo=acta_evaluacion&seccion_id=<?php echo $sec['id']; ?>" target="_blank" class="btn-print-doc btn-sm">
                                🖨️ Acta Oficial Imprimible
                            </a>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <?php if (count($alumnos) === 0): ?>
                            <p class="text-muted" style="text-align:center;padding:20px 0;">No hay alumnos formalizados en esta sección hasta el momento.</p>
                        <?php else: ?>
                            <form method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="seccion_id" value="<?php echo $sec['id']; ?>">
                                
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Nº</th>
                                                <th>Cédula</th>
                                                <th>Estudiante</th>
                                                <th style="width:110px;text-align:center;">Calificación (0-20)</th>
                                                <th style="width:90px;text-align:center;">Inasistencia (N/S)</th>
                                                <th style="text-align:center;">Veredicto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $n = 1;
                                            foreach ($alumnos as $alu): 
                                            ?>
                                            <tr>
                                                <td style="color:#888;"><?php echo $n++; ?></td>
                                                <td><strong><?php echo h($alu['tipo_cedula'] . '-' . $alu['numero_documento']); ?></strong></td>
                                                <td><?php echo h($alu['nombres'] . ' ' . $alu['apellidos']); ?></td>
                                                <td style="text-align:center;">
                                                    <input type="number" name="nota[<?php echo $alu['id']; ?>]"
                                                           value="<?php echo $alu['nota'] !== null ? $alu['nota'] : ''; ?>"
                                                           min="0" max="20" style="width:75px;text-align:center;font-weight:bold;padding:6px;border-radius:6px;border:1px solid #cbd5e1;"
                                                           <?php echo $definitiva ? 'readonly' : ''; ?>>
                                                </td>
                                                <td style="text-align:center;">
                                                    <input type="checkbox" name="inasistencia[<?php echo $alu['id']; ?>]"
                                                           <?php echo $alu['inasistencia'] ? 'checked' : ''; ?>
                                                           <?php echo $definitiva ? 'disabled' : ''; ?> style="width:18px;height:18px;cursor:pointer;">
                                                </td>
                                                <td style="text-align:center;">
                                                    <?php if ($alu['inasistencia']): ?>
                                                        <span class="badge badge-danger">Inasistente</span>
                                                    <?php elseif ($alu['nota'] !== null): ?>
                                                        <span class="badge <?php echo $alu['nota'] >= 14 ? 'badge-success' : 'badge-danger'; ?>">
                                                            <?php echo $alu['nota'] >= 14 ? 'Aprobado (' . $alu['nota'] . ')' : 'Reprobado (' . $alu['nota'] . ')'; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="color:#94a3b8;font-size:0.75rem;">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if (!$definitiva): ?>
                                <div style="margin-top:16px;display:flex;gap:12px;justify-content:flex-end;">
                                    <button type="submit" name="guardar_borrador" class="btn-success">
                                        💾 Guardar Borrador
                                    </button>
                                    <button type="submit" name="cerrar_acta" class="btn-primary" onclick="return confirm('¿Está seguro de cerrar el acta de forma definitiva? Luego del cierre no podrá modificarse sin autorización de Control de Estudios.')">
                                        🔒 Cierre Definitivo de Acta
                                    </button>
                                </div>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
