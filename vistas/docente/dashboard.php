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

// Obtener todos los horarios asignados al docente
$stmtHor = $pdo->prepare("SELECT h.*, sec.seccion, sec.aula, sec.periodo,
                                 asig.nombre as materia_nombre, asig.codigo as materia_codigo, asig.uc,
                                 pl.nombre as plan_nombre, s.nombre as sede_nombre,
                                 (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = sec.id AND i.estatus = 'Formalizada') as total_alumnos
                          FROM horarios h
                          JOIN secciones sec ON sec.id = h.seccion_id
                          JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                          JOIN plan_estudios pl ON pl.id = sec.plan_id
                          JOIN sedes s ON s.id = sec.sede_id
                          WHERE sec.profesor_id = :prof AND sec.activa = true
                          ORDER BY h.dia_semana, h.hora_inicio");
$stmtHor->execute([':prof' => $docente_id]);
$horarios_docente = $stmtHor->fetchAll();

// Mapear horarios por sección y por día
$horarios_por_seccion = [];
$horarios_por_dia = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => []];
$dias_activos = [];
$minutos_totales = 0;

foreach ($horarios_docente as $hd) {
    $horarios_por_seccion[$hd['seccion_id']][] = $hd;
    $d = (int)$hd['dia_semana'];
    if (isset($horarios_por_dia[$d])) {
        $horarios_por_dia[$d][] = $hd;
    }
    $dias_activos[$d] = true;
    $t_ini = strtotime($hd['hora_inicio']);
    $t_fin = strtotime($hd['hora_fin']);
    if ($t_fin > $t_ini) {
        $minutos_totales += ($t_fin - $t_ini) / 60;
    }
}
$horas_semanales = round($minutos_totales / 60, 1);

$titulo = 'Carga Académica y Horarios | Docente';
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
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>📚 Mis Asignaturas y Actas</span></a>
            <a href="#modulo-horario" data-modulo="horario"><span>📅 Mi Horario de Clases</span></a>
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

        <section id="modulo-inicio" class="module-section">
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
                            <span style="font-size:0.75rem;color:var(--text-muted);">
                                <?php echo h($sec['plan_nombre']); ?> &middot; <?php echo h($sec['sede_nombre']); ?> &middot; Sección: <strong><?php echo h($sec['seccion']); ?></strong> &middot; <?php echo $sec['uc']; ?> UC &middot; Aula: <?php echo h($sec['aula'] ?: 'Virtual'); ?>
                            </span>
                            <?php if (!empty($horarios_por_seccion[$sec['id']])): ?>
                                <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;">
                                    <?php foreach ($horarios_por_seccion[$sec['id']] as $sh): ?>
                                        <span class="badge-horario">
                                            📅 <?php echo nombre_dia_semana((int)$sh['dia_semana']); ?>: <?php echo formatear_rango_horario($sh['hora_inicio'], $sh['hora_fin']); ?>
                                            <?php if (!empty($sh['aula'])): ?> &middot; Aula: <?php echo h($sh['aula']); ?><?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
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
        </section>

        <!-- ===== MÓDULO: HORARIO DE CLASES DOCENTE ===== -->
        <section id="modulo-horario" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div>
                        <h3>📅 Mi Cronograma y Horario Semanal de Clases</h3>
                        <p style="font-size:0.85rem;color:var(--text-muted);margin:4px 0 0 0;">
                            Distribución de bloques horarios, aulas asignadas y carga académica semanal.
                        </p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button class="btn-outline btn-sm" onclick="window.print()">🖨️ Imprimir Horario</button>
                    </div>
                </div>
                <div class="dashboard-card-body">
                    <!-- Resumen de Horario -->
                    <div class="stats-grid" style="margin-bottom:24px;">
                        <div class="stat-card">
                            <div class="stat-icon">⏱️</div>
                            <div class="stat-details">
                                <span class="stat-val"><?php echo $horas_semanales; ?> h</span>
                                <span class="stat-lbl">Horas Académicas / Sem</span>
                            </div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-icon">📅</div>
                            <div class="stat-details">
                                <span class="stat-val"><?php echo count(array_filter($horarios_por_dia)); ?></span>
                                <span class="stat-lbl">Días de Clase Asignados</span>
                            </div>
                        </div>
                        <div class="stat-card info">
                            <div class="stat-icon">🔢</div>
                            <div class="stat-details">
                                <span class="stat-val"><?php echo count($horarios_docente); ?></span>
                                <span class="stat-lbl">Bloques de Horario</span>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($horarios_docente)): ?>
                        <div class="alert alert-info">
                            No se han configurado bloques de horarios para sus secciones en este período. Si requiere programar sus horarios, comuníquese con la Coordinación de Programa.
                        </div>
                    <?php else: ?>
                        <!-- Grilla Visual de Horario Semanal -->
                        <h4 style="margin-bottom:12px;font-weight:600;">🗓️ Vista Semanal</h4>
                        <div class="schedule-timetable">
                            <?php 
                            $dias_semana_nombres = [
                                1 => 'Lunes',
                                2 => 'Martes',
                                3 => 'Miércoles',
                                4 => 'Jueves',
                                5 => 'Viernes',
                                6 => 'Sábado'
                            ];
                            foreach ($dias_semana_nombres as $dia_num => $dia_nombre):
                                $bloques = $horarios_por_dia[$dia_num] ?? [];
                            ?>
                            <div class="schedule-day-col">
                                <div class="schedule-day-header">
                                    <span class="day-name"><?php echo $dia_nombre; ?></span>
                                    <span class="day-badge"><?php echo count($bloques); ?></span>
                                </div>
                                <div class="schedule-day-body">
                                    <?php if (empty($bloques)): ?>
                                        <div class="schedule-empty-slot">Sin clases</div>
                                    <?php else: ?>
                                        <?php foreach ($bloques as $b): ?>
                                            <div class="schedule-item-card">
                                                <div class="schedule-item-time">
                                                    ⏰ <?php echo formatear_rango_horario($b['hora_inicio'], $b['hora_fin']); ?>
                                                </div>
                                                <div class="schedule-item-subject">
                                                    <?php echo h($b['materia_nombre']); ?>
                                                </div>
                                                <div class="schedule-item-meta">
                                                    <span>Sec: <strong><?php echo h($b['seccion']); ?></strong></span>
                                                    <span>Aula: <strong><?php echo h($b['aula'] ?: 'Virtual'); ?></strong></span>
                                                </div>
                                                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">
                                                    👥 <?php echo $b['total_alumnos']; ?> estudiantes
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tabla Detallada -->
                        <h4 style="margin-top:32px;margin-bottom:12px;font-weight:600;">📋 Detalle de Bloques Horarios</h4>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Horario</th>
                                        <th>Asignatura</th>
                                        <th>Sección</th>
                                        <th>Aula</th>
                                        <th>Estudiantes</th>
                                        <th>Programa</th>
                                        <th>Sede</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($horarios_docente as $hd): ?>
                                    <tr>
                                        <td><strong><?php echo nombre_dia_semana((int)$hd['dia_semana']); ?></strong></td>
                                        <td>
                                            <span class="badge-horario">
                                                <?php echo formatear_rango_horario($hd['hora_inicio'], $hd['hora_fin']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo h($hd['materia_nombre']); ?></strong>
                                            <small style="color:var(--text-muted);display:block;"><?php echo h($hd['materia_codigo']); ?> &middot; <?php echo $hd['uc']; ?> UC</small>
                                        </td>
                                        <td>Sec. <?php echo h($hd['seccion']); ?></td>
                                        <td><span class="badge badge-navy"><?php echo h($hd['aula'] ?: 'Virtual'); ?></span></td>
                                        <td><?php echo $hd['total_alumnos']; ?> formalizados</td>
                                        <td><small><?php echo h($hd['plan_nombre']); ?></small></td>
                                        <td><small><?php echo h($hd['sede_nombre']); ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
