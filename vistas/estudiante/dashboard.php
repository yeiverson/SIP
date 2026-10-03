<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(6);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

iniciar_sesion_segura();

$usuario_id = (int)$_SESSION['usuario_id'];
$nombre_est  = $_SESSION['nombre_full'];
$sede_id     = (int)($_SESSION['sede_id'] ?? 0);
$mensaje     = '';

// Obtener info de la sede
$sede_info = null;
if ($sede_id > 0) {
    $stmtS = $pdo->prepare("SELECT * FROM sedes WHERE id = :id");
    $stmtS->execute([':id' => $sede_id]);
    $sede_info = $stmtS->fetch();
}

// Obtener datos académicos y kardex del estudiante
$stmtEst = $pdo->prepare("SELECT u.*, s.nombre as sede_nombre, pl.nombre as plan_nombre, pl.codigo as plan_codigo,
                                 ROUND(AVG(CASE WHEN an.estatus='Definitiva' THEN an.nota END), 2) as promedio_acumulado,
                                 COALESCE(SUM(CASE WHEN an.estatus='Definitiva' AND an.nota >= 14 THEN asig.uc ELSE 0 END), 0) as uc_aprobadas,
                                 COALESCE(SUM(CASE WHEN an.estatus='Definitiva' AND an.nota < 14 AND an.nota IS NOT NULL THEN asig.uc ELSE 0 END), 0) as uc_reprobadas
                          FROM usuarios u
                          LEFT JOIN sedes s ON s.id = u.sede_id
                          LEFT JOIN plan_estudios pl ON pl.id = u.plan_id
                          LEFT JOIN actas_notas an ON an.usuario_id = u.id AND an.estatus = 'Definitiva'
                          LEFT JOIN secciones sec ON sec.id = an.seccion_id
                          LEFT JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                          WHERE u.id = :uid
                          GROUP BY u.id, s.nombre, pl.nombre, pl.codigo");
$stmtEst->execute([':uid' => $usuario_id]);
$estudiante = $stmtEst->fetch();

// Créditos resguardados
$stmtCred = $pdo->prepare("SELECT COALESCE(SUM(uc_resguardadas), 0) as total FROM creditos_resguardados WHERE usuario_id = :uid AND estatus = 'Activo'");
$stmtCred->execute([':uid' => $usuario_id]);
$uc_resguardadas = (int)$stmtCred->fetchColumn();

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $mensaje = alerta_error('Token de seguridad inválido. Recargue la página.');
    } else {
        // 1. Inscribir materia
        if (isset($_POST['inscribir_materia'])) {
            $seccion_id = (int)$_POST['seccion_id'];
            try {
                $pdo->beginTransaction();

                // Validar si ya está inscrita
                $stmtCheck = $pdo->prepare("SELECT id, estatus FROM inscripciones WHERE usuario_id = :uid AND seccion_id = :sid");
                $stmtCheck->execute([':uid' => $usuario_id, ':sid' => $seccion_id]);
                $existeInsc = $stmtCheck->fetch();
                if ($existeInsc && $existeInsc['estatus'] !== 'Eliminada') {
                    throw new Exception('Ya posee esta sección inscrita o en proceso de formalización.');
                }

                // Validar cupo
                $sec = $pdo->prepare("SELECT cupo_maximo, (SELECT COUNT(*) FROM inscripciones WHERE seccion_id = :sid AND estatus != 'Eliminada') as inscritos FROM secciones WHERE id = :sid2");
                $sec->execute([':sid' => $seccion_id, ':sid2' => $seccion_id]);
                $sec_data = $sec->fetch();
                if ($sec_data && $sec_data['inscritos'] >= $sec_data['cupo_maximo']) {
                    throw new Exception('La sección seleccionada ha agotado sus cupos disponibles.');
                }

                // Validar colisión de horarios con materias actualmente activas
                $stmtHorariosNuevos = $pdo->prepare("SELECT * FROM horarios WHERE seccion_id = :sid");
                $stmtHorariosNuevos->execute([':sid' => $seccion_id]);
                $nuevos_horarios = $stmtHorariosNuevos->fetchAll();

                $stmtInscActivas = $pdo->prepare("SELECT seccion_id FROM inscripciones WHERE usuario_id = :uid AND estatus IN ('Formalizada', 'Por Cancelar')");
                $stmtInscActivas->execute([':uid' => $usuario_id]);
                $secciones_actuales = $stmtInscActivas->fetchAll(PDO::FETCH_COLUMN);

                foreach ($secciones_actuales as $sec_activa_id) {
                    $stmtHorariosExistentes = $pdo->prepare("SELECT * FROM horarios WHERE seccion_id = :sid");
                    $stmtHorariosExistentes->execute([':sid' => $sec_activa_id]);
                    $horarios_exist = $stmtHorariosExistentes->fetchAll();

                    foreach ($horarios_exist as $he) {
                        foreach ($nuevos_horarios as $nh) {
                            if ($he['dia_semana'] == $nh['dia_semana'] &&
                                $he['hora_inicio'] < $nh['hora_fin'] &&
                                $nh['hora_inicio'] < $he['hora_fin']) {
                                throw new Exception('Existe un choque de horario con otra asignatura ya seleccionada.');
                            }
                        }
                    }
                }

                // Crear o reactivar inscripción
                if ($existeInsc && $existeInsc['estatus'] === 'Eliminada') {
                    $stmtIns = $pdo->prepare("UPDATE inscripciones SET estatus = 'Por Cancelar', updated_at = NOW() WHERE id = :id");
                    $stmtIns->execute([':id' => $existeInsc['id']]);
                    $insc_id = $existeInsc['id'];
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO inscripciones (usuario_id, seccion_id, estatus) VALUES (:uid, :sid, 'Por Cancelar') RETURNING id");
                    $stmtIns->execute([':uid' => $usuario_id, ':sid' => $seccion_id]);
                    $insc_id = $stmtIns->fetchColumn();
                }

                $pdo->commit();
                registrar_log($pdo, 'Inscripción materia', 'inscripciones', $insc_id, "Estudiante $usuario_id seleccionó sección $seccion_id");
                $mensaje = alerta_success('Asignatura seleccionada correctamente. Reporte su pago en la pestaña correspondiente para formalizarla.');
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = alerta_error($e->getMessage());
            }
        }
        // 2. Registrar comprobante de pago por transferencia o pago móvil
        elseif (isset($_POST['reportar_pago'])) {
            $referencia = trim($_POST['referencia'] ?? '');
            $banco = trim($_POST['banco'] ?? '');
            $monto = (float)($_POST['monto'] ?? 0);
            $fecha = $_POST['fecha_pago'] ?? date('Y-m-d');

            if ($referencia && $banco && $monto > 0) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO pagos (usuario_id, banco, referencia, monto, fecha_pago)
                            VALUES (:uid, :banco, :ref, :monto, :fp) RETURNING id");
                    $stmt->execute([
                        ':uid'   => $usuario_id,
                        ':banco' => $banco,
                        ':ref'   => $referencia,
                        ':monto' => $monto,
                        ':fp'    => $fecha,
                    ]);
                    $pago_id = $stmt->fetchColumn();
                    registrar_log($pdo, 'Reporte de pago estudiante', 'pagos', $pago_id, "Ref: $referencia, Monto: $monto");
                    $mensaje = alerta_success("¡Pago reportado con éxito! El personal de Secretaría validará la referencia '$referencia' para formalizar su inscripción.");
                } catch (PDOException $e) {
                    $mensaje = ($e->getCode() === '23505')
                        ? alerta_error("Esta referencia bancaria ya ha sido registrada previamente en el sistema.")
                        : alerta_error('Error al registrar el pago: ' . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error('Por favor complete todos los datos requeridos para registrar el pago.');
            }
        }
    }
}

// Inscripciones activas del estudiante
$stmtInsc = $pdo->prepare("SELECT i.*, sec.seccion, sec.aula, asig.nombre as materia, asig.codigo as materia_codigo, asig.uc,
                                  s.nombre as sede_nombre, pl.nombre as plan_nombre,
                                  CONCAT(p.tipo_cedula, '-', p.numero_documento, ' | ', p.nombres, ' ', p.apellidos) as profesor
                           FROM inscripciones i
                           JOIN secciones sec ON sec.id = i.seccion_id
                           JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                           JOIN sedes s ON s.id = sec.sede_id
                           JOIN plan_estudios pl ON pl.id = sec.plan_id
                           LEFT JOIN usuarios p ON p.id = sec.profesor_id
                           WHERE i.usuario_id = :uid AND i.estatus != 'Eliminada'
                           ORDER BY i.created_at DESC");
$stmtInsc->execute([':uid' => $usuario_id]);
$inscripciones = $stmtInsc->fetchAll();

// Oferta académica disponible (si la sede está en Fase 2 de inscripciones)
$faseActual = (int)($sede_info['fase_actual'] ?? 1);
$oferta = [];
if ($sede_id > 0 && $faseActual === 2) {
    $stmtOferta = $pdo->prepare("SELECT sec.*, asig.nombre as materia, asig.codigo as materia_codigo, asig.uc,
                                        CONCAT(p.nombres, ' ', p.apellidos) as profesor,
                                        (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = sec.id AND i.estatus != 'Eliminada') as inscritos
                                 FROM secciones sec
                                 JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                                 LEFT JOIN usuarios p ON p.id = sec.profesor_id
                                 WHERE sec.sede_id = :sede AND sec.activa = true
                                 ORDER BY asig.nombre");
    $stmtOferta->execute([':sede' => $sede_id]);
    $oferta = $stmtOferta->fetchAll();
}

// Historial de notas consolidadas (Kardex)
$stmtNotas = $pdo->prepare("SELECT an.*, asig.nombre as materia, asig.codigo as materia_codigo, asig.uc, sec.seccion, sec.periodo
                            FROM actas_notas an
                            JOIN secciones sec ON sec.id = an.seccion_id
                            JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                            WHERE an.usuario_id = :uid AND an.estatus = 'Definitiva'
                            ORDER BY an.updated_at DESC");
$stmtNotas->execute([':uid' => $usuario_id]);
$notas = $stmtNotas->fetchAll();

// Pagos registrados del estudiante
$stmtMisPagos = $pdo->prepare("SELECT * FROM pagos WHERE usuario_id = :uid ORDER BY created_at DESC");
$stmtMisPagos->execute([':uid' => $usuario_id]);
$mis_pagos = $stmtMisPagos->fetchAll();

// Horario de clases del estudiante para asignaturas formalizadas y por cancelar
$stmtHorEst = $pdo->prepare("SELECT h.*, sec.seccion, sec.aula, sec.periodo,
                                    asig.nombre as materia_nombre, asig.codigo as materia_codigo, asig.uc,
                                    pl.nombre as plan_nombre, s.nombre as sede_nombre,
                                    CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre,
                                    i.estatus as inscripcion_estatus
                             FROM inscripciones i
                             JOIN secciones sec ON sec.id = i.seccion_id
                             JOIN horarios h ON h.seccion_id = sec.id
                             JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                             JOIN plan_estudios pl ON pl.id = sec.plan_id
                             JOIN sedes s ON s.id = sec.sede_id
                             LEFT JOIN usuarios p ON p.id = sec.profesor_id
                             WHERE i.usuario_id = :uid AND i.estatus IN ('Formalizada', 'Por Cancelar')
                             ORDER BY h.dia_semana, h.hora_inicio");
$stmtHorEst->execute([':uid' => $usuario_id]);
$horarios_estudiante = $stmtHorEst->fetchAll();

// Mapear por día y por sección
$horarios_est_por_dia = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => []];
$horarios_est_por_seccion = [];
$dias_activos_est = [];
$minutos_totales_est = 0;
foreach ($horarios_estudiante as $he) {
    $horarios_est_por_seccion[$he['seccion_id']][] = $he;
    $d = (int)$he['dia_semana'];
    if (isset($horarios_est_por_dia[$d])) {
        $horarios_est_por_dia[$d][] = $he;
    }
    $dias_activos_est[$d] = true;
    $t_ini = strtotime($he['hora_inicio']);
    $t_fin = strtotime($he['hora_fin']);
    if ($t_fin > $t_ini) {
        $minutos_totales_est += ($t_fin - $t_ini) / 60;
    }
}
$horas_semanales_est = round($minutos_totales_est / 60, 1);

$titulo = 'Portal del Estudiante';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">Estudiante Regular</span>
            </div>
            <p><?php echo h($nombre_est); ?></p>
            <p><small><?php echo h($sede_info['nombre'] ?? 'Sede Central'); ?></small></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Mi Expediente</span></a>
            <a href="#modulo-horario" data-modulo="horario"><span>📅 Mi Horario de Clases</span></a>
            <a href="#modulo-inscripciones" data-modulo="inscripciones"><span>📝 Inscripción de Materias</span></a>
            <a href="#modulo-pagos" data-modulo="pagos"><span>💳 Registro de Pagos</span></a>
            <a href="#modulo-kardex" data-modulo="kardex"><span>📊 Historial Académico</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div>
                <h2>Portal Académico del Estudiante</h2>
                <p>Programa: <strong><?php echo h($estudiante['plan_nombre'] ?? 'Postgrado UNEFA'); ?></strong> &middot; <?php echo fecha_hoy_formateada(); ?></p>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="../../controlador/reportes.php?tipo=constancia_estudio" target="_blank" class="btn-print-doc btn-sm">
                    📜 Constancia de Estudio
                </a>
                <a href="../../controlador/reportes.php?tipo=comprobante_inscripcion" target="_blank" class="btn-outline btn-sm">
                    📑 Comprobante Oficial
                </a>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <!-- METRICAS ACADÉMICAS -->
        <div class="stats-grid">
            <div class="stat-card gold">
                <div class="stat-icon">⭐</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $estudiante['promedio_acumulado'] ? number_format($estudiante['promedio_acumulado'], 2) : '—'; ?></span>
                    <span class="stat-lbl">Promedio Ponderado</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">🎓</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $estudiante['uc_aprobadas']; ?> UC</span>
                    <span class="stat-lbl">Créditos Aprobados</span>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">📚</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($inscripciones); ?></span>
                    <span class="stat-lbl">Materias en Curso</span>
                </div>
            </div>
            <?php if ($uc_resguardadas > 0): ?>
            <div class="stat-card warning">
                <div class="stat-icon">🛡️</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $uc_resguardadas; ?> UC</span>
                    <span class="stat-lbl">Créditos Resguardados</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== ASIGNATURAS INSCRITAS ESTE PERÍODO ===== -->
        <section id="modulo-inicio" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📚 Mis Asignaturas en el Período Actual</h3>
                </div>
                <div class="dashboard-card-body">
                    <?php if (count($inscripciones) === 0): ?>
                        <div class="alert alert-info">Actualmente no posee asignaturas inscritas. Ingrese a la sección de "Inscripción de Materias" para seleccionar su carga académica.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asignatura</th>
                                    <th>Sección</th>
                                    <th>U.C.</th>
                                    <th>Aula</th>
                                    <th>Docente Asignado</th>
                                    <th style="text-align:center;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripciones as $i): ?>
                                <tr>
                                    <td><strong><?php echo h($i['materia_codigo']); ?></strong></td>
                                    <td>
                                        <strong><?php echo h($i['materia']); ?></strong>
                                        <?php if (!empty($horarios_est_por_seccion[$i['seccion_id']])): ?>
                                            <div style="margin-top:4px;display:flex;gap:4px;flex-wrap:wrap;">
                                                <?php foreach ($horarios_est_por_seccion[$i['seccion_id']] as $sh): ?>
                                                    <span class="badge-horario" style="font-size:0.7rem;padding:2px 8px;">
                                                        📅 <?php echo nombre_dia_semana((int)$sh['dia_semana']); ?>: <?php echo formatear_rango_horario($sh['hora_inicio'], $sh['hora_fin']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>Sec. <?php echo h($i['seccion']); ?></td>
                                    <td><strong><?php echo $i['uc']; ?> UC</strong></td>
                                    <td><?php echo h($i['aula'] ?: 'Virtual'); ?></td>
                                    <td><?php echo h($i['profesor'] ?: 'Por designar'); ?></td>
                                    <td style="text-align:center;">
                                        <?php if ($i['estatus'] === 'Formalizada'): ?>
                                            <span class="badge badge-success">🟢 Formalizada</span>
                                        <?php elseif ($i['estatus'] === 'Por Cancelar'): ?>
                                            <span class="badge badge-warning">🟡 Por Cancelar</span>
                                        <?php else: ?>
                                            <span class="badge badge-navy"><?php echo h($i['estatus']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: HORARIO DE CLASES DEL ESTUDIANTE ===== -->
        <section id="modulo-horario" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div>
                        <h3>📅 Mi Horario y Cronograma Semanal de Clases</h3>
                        <p style="font-size:0.85rem;color:var(--text-muted);margin:4px 0 0 0;">
                            Horarios de clases asignados para sus asignaturas inscritas y formalizadas.
                        </p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button class="btn-outline btn-sm" onclick="window.print()">🖨️ Imprimir Horario</button>
                    </div>
                </div>
                <div class="dashboard-card-body">
                    <!-- Resumen de Horario Estudiante -->
                    <div class="stats-grid" style="margin-bottom:24px;">
                        <div class="stat-card">
                            <div class="stat-icon">⏱️</div>
                            <div class="stat-details">
                                <span class="stat-val"><?php echo $horas_semanales_est; ?> h</span>
                                <span class="stat-lbl">Horas Académicas / Sem</span>
                            </div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-icon">📅</div>
                            <div class="stat-details">
                                <span class="stat-val"><?php echo count(array_filter($horarios_est_por_dia)); ?></span>
                                <span class="stat-lbl">Días de Asistencia</span>
                            </div>
                        </div>
                        <div class="stat-card info">
                            <div class="stat-icon">📚</div>
                            <div class="stat-details">
                                <span class="stat-val"><?php echo count($horarios_estudiante); ?></span>
                                <span class="stat-lbl">Bloques de Clase</span>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($horarios_estudiante)): ?>
                        <div class="alert alert-info">
                            No posee horarios registrados para sus asignaturas en curso. Una vez inscritas y formalizadas sus materias con secciones activas, sus bloques de clase se reflejarán automáticamente en esta grilla.
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
                                $bloques = $horarios_est_por_dia[$dia_num] ?? [];
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
                                                <?php if (!empty($b['docente_nombre'])): ?>
                                                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">
                                                    👨‍🏫 <?php echo h($b['docente_nombre']); ?>
                                                </div>
                                                <?php endif; ?>
                                                <div style="margin-top:4px;">
                                                    <?php if ($b['inscripcion_estatus'] === 'Formalizada'): ?>
                                                        <span class="badge badge-success" style="font-size:0.68rem;padding:2px 6px;">Formalizada</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning" style="font-size:0.68rem;padding:2px 6px;">Por Formalizar</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tabla Detallada -->
                        <h4 style="margin-top:32px;margin-bottom:12px;font-weight:600;">📋 Detalle de Bloques y Aulas</h4>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Horario</th>
                                        <th>Asignatura</th>
                                        <th>Sección</th>
                                        <th>Aula</th>
                                        <th>Docente</th>
                                        <th>Programa</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($horarios_estudiante as $he): ?>
                                    <tr>
                                        <td><strong><?php echo nombre_dia_semana((int)$he['dia_semana']); ?></strong></td>
                                        <td>
                                            <span class="badge-horario">
                                                <?php echo formatear_rango_horario($he['hora_inicio'], $he['hora_fin']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo h($he['materia_nombre']); ?></strong>
                                            <small style="color:var(--text-muted);display:block;"><?php echo h($he['materia_codigo']); ?> &middot; <?php echo $he['uc']; ?> UC</small>
                                        </td>
                                        <td>Sec. <?php echo h($he['seccion']); ?></td>
                                        <td><span class="badge badge-navy"><?php echo h($he['aula'] ?: 'Virtual'); ?></span></td>
                                        <td><?php echo h($he['docente_nombre'] ?: 'Por designar'); ?></td>
                                        <td><small><?php echo h($he['plan_nombre']); ?></small></td>
                                        <td>
                                            <?php if ($he['inscripcion_estatus'] === 'Formalizada'): ?>
                                                <span class="badge badge-success">Formalizada</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Por Cancelar</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: OFERTA E INSCRIPCIÓN ===== -->
        <section id="modulo-inscripciones" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📝 Oferta Académica Disponible para Inscripción</h3>
                    <span class="badge <?php echo $faseActual === 2 ? 'badge-success' : 'badge-warning'; ?>">
                        <?php echo $faseActual === 2 ? '🟢 Fase 2: Inscripciones Abiertas' : '🟡 Fase 1: Período de Planificación (Inscripciones Cerradas)'; ?>
                    </span>
                </div>
                <div class="dashboard-card-body">
                    <?php if ($faseActual !== 2): ?>
                        <div class="alert alert-warning">
                            El período formal de inscripciones para su sede aún no está abierto. El Coordinador de Programa está estructurando la oferta académica. Esté atento a los comunicados oficiales.
                        </div>
                    <?php elseif (count($oferta) === 0): ?>
                        <div class="alert alert-info">No se encontraron secciones activas ofertadas para su sede en este momento.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Asignatura</th>
                                        <th>Sección</th>
                                        <th>U.C.</th>
                                        <th>Docente</th>
                                        <th>Cupos</th>
                                        <th style="text-align:center;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($oferta as $sec): ?>
                                    <?php 
                                    $yaInscrita = false;
                                    foreach ($inscripciones as $ins) {
                                        if ((int)$ins['seccion_id'] === (int)$sec['id']) {
                                            $yaInscrita = true;
                                            break;
                                        }
                                    }
                                    $sinCupo = ($sec['inscritos'] >= $sec['cupo_maximo']);
                                    ?>
                                    <tr>
                                        <td><strong><?php echo h($sec['materia_codigo']); ?></strong></td>
                                        <td><?php echo h($sec['materia']); ?></td>
                                        <td>Sec. <?php echo h($sec['seccion']); ?></td>
                                        <td><strong><?php echo $sec['uc']; ?> UC</strong></td>
                                        <td><?php echo h($sec['profesor'] ?: 'Por designar'); ?></td>
                                        <td><?php echo $sec['inscritos']; ?> / <?php echo $sec['cupo_maximo']; ?></td>
                                        <td style="text-align:center;">
                                            <?php if ($yaInscrita): ?>
                                                <span class="badge badge-success">✓ Seleccionada</span>
                                            <?php elseif ($sinCupo): ?>
                                                <span class="badge badge-danger">Agotada</span>
                                            <?php else: ?>
                                                <form method="POST" style="display:inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="inscribir_materia" value="1">
                                                    <input type="hidden" name="seccion_id" value="<?php echo $sec['id']; ?>">
                                                    <button type="submit" class="btn-primary btn-sm">➕ Seleccionar</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: REPORTE DE PAGOS ===== -->
        <section id="modulo-pagos" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>💳 Registro y Comprobación de Pagos de Arancel</h3>
                </div>
                <div class="dashboard-card-body">
                    <p style="color:#666;font-size:0.85rem;margin-bottom:16px;">
                        Reporte los datos de la transferencia o pago móvil efectuado para que el departamento de Control de Estudios valide y formalice sus asignaturas inscritas.
                    </p>
                    <form method="POST" style="background:#f8fafc;padding:20px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:24px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="reportar_pago" value="1">
                        <div class="form-grid-3">
                            <div class="input-group">
                                <label>Banco Emisor / Modalidad:</label>
                                <select name="banco" required>
                                    <option value="Banco de Venezuela">Banco de Venezuela</option>
                                    <option value="Banco Bicentenario">Banco Bicentenario</option>
                                    <option value="Banco Mercantil">Banco Mercantil</option>
                                    <option value="Banesco">Banesco</option>
                                    <option value="Banco Provincial">Banco Provincial</option>
                                    <option value="Pago Móvil Interbancario">Pago Móvil Interbancario</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Número de Referencia Bancaria:</label>
                                <input type="text" name="referencia" placeholder="Ej: 98765432" required>
                            </div>
                            <div class="input-group">
                                <label>Monto Pagado (Bs.):</label>
                                <input type="number" step="0.01" name="monto" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="form-grid-2">
                            <div class="input-group">
                                <label>Fecha de la Operación:</label>
                                <input type="date" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-success">📨 Registrar Pago de Inscripción</button>
                    </form>

                    <h4>Historial de Pagos Registrados</h4>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Banco</th>
                                    <th>Referencia</th>
                                    <th>Monto</th>
                                    <th>Estado de Validación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($mis_pagos) === 0): ?>
                                <tr><td colspan="5" class="text-center">No tiene pagos registrados en el sistema.</td></tr>
                                <?php else: ?>
                                <?php foreach ($mis_pagos as $p): ?>
                                <tr>
                                    <td><?php echo formatear_fecha($p['fecha_pago'] ?? $p['created_at']); ?></td>
                                    <td><?php echo h($p['banco'] ?: 'Transferencia'); ?></td>
                                    <td><strong><?php echo h($p['referencia'] ?: ($p['referencia_bancaria'] ?? '—')); ?></strong></td>
                                    <td>Bs. <?php echo number_format((float)$p['monto'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge badge-success">✓ Registrado en Sistema</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: KARDEX ACADÉMICO ===== -->
        <section id="modulo-kardex" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📊 Kardex Académico Oficial y Calificaciones Obtenidas</h3>
                </div>
                <div class="dashboard-card-body">
                    <?php if (count($notas) === 0): ?>
                        <p class="text-muted" style="text-align:center;padding:20px 0;">Aún no posee actas definitivas de calificación consolidadas para este programa.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Asignatura Cursada</th>
                                    <th>Sección / Período</th>
                                    <th>U.C.</th>
                                    <th style="text-align:center;">Nota (0-20)</th>
                                    <th style="text-align:center;">Condición</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas as $not): ?>
                                <tr>
                                    <td><strong><?php echo h($not['materia_codigo']); ?></strong></td>
                                    <td><?php echo h($not['materia']); ?></td>
                                    <td>Sec. <?php echo h($not['seccion']); ?> (<?php echo h($not['periodo'] ?: '2026-I'); ?>)</td>
                                    <td><?php echo $not['uc']; ?> UC</td>
                                    <td style="text-align:center;font-size:1.1rem;font-weight:bold;">
                                        <?php echo $not['inasistencia'] ? 'N/S' : sprintf('%02d', $not['nota']); ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if ($not['inasistencia']): ?>
                                            <span class="badge badge-danger">Inasistente</span>
                                        <?php elseif ($not['nota'] >= 14): ?>
                                            <span class="badge badge-success">Aprobado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Reprobado</span>
                                        <?php endif; ?>
                                    </td>
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
