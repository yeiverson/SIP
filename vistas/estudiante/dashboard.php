<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(6);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$usuario_id = $_SESSION['usuario_id'];
$nombre_est = $_SESSION['nombre_full'];
$sede_id = $_SESSION['sede_id'];
$mensaje = '';

// Obtener info sede
$sede_info = $pdo->prepare("SELECT * FROM sedes WHERE id = :id");
$sede_info->execute([':id' => $sede_id]);
$sede = $sede_info->fetch();

// Obtener datos del estudiante
$est = $pdo->prepare("SELECT u.*, ROUND(AVG(CASE WHEN an.estatus='Definitiva' THEN an.nota END),1) as promedio,
                       SUM(CASE WHEN an.estatus='Definitiva' AND an.nota >= 14 THEN asig.uc ELSE 0 END) as uc_aprobadas,
                       SUM(CASE WHEN an.estatus='Definitiva' AND an.nota < 14 THEN asig.uc ELSE 0 END) as uc_reprobadas
                       FROM usuarios u
                       LEFT JOIN inscripciones i ON i.usuario_id = u.id AND i.estatus = 'Formalizada'
                       LEFT JOIN secciones sec ON sec.id = i.seccion_id
                       LEFT JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                       LEFT JOIN actas_notas an ON an.seccion_id = i.seccion_id AND an.usuario_id = u.id AND an.estatus = 'Definitiva'
                       WHERE u.id = :uid
                       GROUP BY u.id");
$est->execute([':uid' => $usuario_id]);
$estudiante = $est->fetch();

// Créditos resguardados
$creditos = $pdo->prepare("SELECT SUM(uc_resguardadas) as total FROM creditos_resguardados WHERE usuario_id = :uid AND estatus = 'Activo'");
$creditos->execute([':uid' => $usuario_id]);
$cred = $creditos->fetch();
$uc_resguardadas = (int)($cred['total'] ?? 0);

// Inscripciones activas
$inscripciones = $pdo->prepare("SELECT i.*, sec.seccion, asig.nombre as materia, asig.codigo as materia_codigo, asig.uc,
                                s.nombre as sede_nombre, pl.nombre as plan_nombre,
                                CONCAT(p.tipo_cedula,'-',p.numero_documento,' | ',p.nombres,' ',p.apellidos) as profesor
                                FROM inscripciones i
                                JOIN secciones sec ON sec.id = i.seccion_id
                                JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                                JOIN sedes s ON s.id = sec.sede_id
                                JOIN plan_estudios pl ON pl.id = sec.plan_id
                                LEFT JOIN usuarios p ON p.id = sec.profesor_id
                                WHERE i.usuario_id = :uid
                                ORDER BY i.created_at DESC");
$inscripciones->execute([':uid' => $usuario_id]);
$inscripciones = $inscripciones->fetchAll();

// Oferta disponible (Fase 2)
$oferta = [];
if ($sede && $sede['fase_actual'] == 2) {
    $oferta = $pdo->prepare("SELECT sec.*, asig.nombre as materia, asig.codigo as materia_codigo, asig.uc,
                              CONCAT(p.tipo_cedula,'-',p.numero_documento,' | ',p.nombres,' ',p.apellidos) as profesor,
                              (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = sec.id AND i.estatus != 'Eliminada') as inscritos
                              FROM secciones sec
                              JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                              LEFT JOIN usuarios p ON p.id = sec.profesor_id
                              WHERE sec.sede_id = :sede AND sec.activa = true
                              ORDER BY asig.nombre");
    $oferta->execute([':sede' => $sede_id]);
    $oferta = $oferta->fetchAll();
}

// Notas
$notas = $pdo->prepare("SELECT an.*, asig.nombre as materia, asig.codigo as materia_codigo, asig.uc, sec.seccion, pl.nombre as plan_nombre
                         FROM actas_notas an
                         JOIN secciones sec ON sec.id = an.seccion_id
                         JOIN inscripciones i ON i.seccion_id = sec.id AND i.usuario_id = an.usuario_id
                         JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                         JOIN plan_estudios pl ON pl.id = sec.plan_id
                         WHERE an.usuario_id = :uid AND an.estatus = 'Definitiva'
                         ORDER BY an.updated_at DESC");
$notas->execute([':uid' => $usuario_id]);
$notas = $notas->fetchAll();

// Procesar inscripción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscribir_materia'])) {
    $seccion_id = (int)$_POST['seccion_id'];
    try {
        $pdo->beginTransaction();
        // Validar cupo
        $sec = $pdo->prepare("SELECT cupo_maximo, (SELECT COUNT(*) FROM inscripciones WHERE seccion_id = :sid AND estatus != 'Eliminada') as inscritos FROM secciones WHERE id = :sid2");
        $sec->execute([':sid' => $seccion_id, ':sid2' => $seccion_id]);
        $sec_data = $sec->fetch();
        if ($sec_data['inscritos'] >= $sec_data['cupo_maximo']) {
            throw new Exception('Sección agotada.');
        }
        // Validar choque horario
        $horarios = $pdo->prepare("SELECT * FROM horarios WHERE seccion_id = :sid");
        $horarios->execute([':sid' => $seccion_id]);
        $nuevos_horarios = $horarios->fetchAll();
        foreach ($inscripciones as $insc) {
            if ($insc['estatus'] !== 'Eliminada') {
                $h_exist = $pdo->prepare("SELECT * FROM horarios WHERE seccion_id = :sid");
                $h_exist->execute([':sid' => $insc['id']]);
                while ($h = $h_exist->fetch()) {
                    foreach ($nuevos_horarios as $nh) {
                        if ($h['dia_semana'] == $nh['dia_semana'] &&
                            $h['hora_inicio'] < $nh['hora_fin'] &&
                            $nh['hora_inicio'] < $h['hora_fin']) {
                            throw new Exception('Choque de horario con ' . $insc['materia']);
                        }
                    }
                }
            }
        }
        // Crear inscripción
        $stmt = $pdo->prepare("INSERT INTO inscripciones (usuario_id, seccion_id, estatus) VALUES (:uid, :sid, 'Por Cancelar')");
        $stmt->execute([':uid' => $usuario_id, ':sid' => $seccion_id]);
        $pdo->commit();
        registrar_log($pdo, 'Inscripción materia', 'inscripciones', $pdo->lastInsertId());
        $mensaje = alerta_success('Materia seleccionada. Estado: POR CANCELAR. Diríjase a Secretaría para formalizar el pago.');
        // Recargar
        $inscripciones->execute([':uid' => $usuario_id]);
        $inscripciones = $inscripciones->fetchAll();
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje = alerta_error($e->getMessage());
    }
}

$titulo = 'Panel Estudiante';
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
            <p><?php echo h($nombre_est); ?></p>
            <p><small><?php echo h($sede['nombre'] ?? 'Sin sede'); ?></small></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>📊 Inicio</span></a>
            <a href="#modulo-inscripcion" data-modulo="inscripcion"><span>📝 Inscripción</span></a>
            <a href="#modulo-horario" data-modulo="horario"><span>📅 Mi Horario</span></a>
            <a href="#modulo-notas" data-modulo="notas"><span>📋 Mis Notas</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Portal del Estudiante</h2>
            <p><?php echo fecha_hoy_formateada(); ?>
               &middot; Sede: <?php echo h($sede['nombre'] ?? 'N/A'); ?>
               &middot; Fase: <?php echo ($sede['fase_actual']??1) == 2 ? 'Inscripciones Abiertas' : 'Planificación'; ?>
            </p>
        </header>

        <?php echo $mensaje; ?>

        <section id="modulo-inicio" class="module-section">
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon blue">🎓</div>
                    <div class="stat-info">
                        <strong><?php echo (int)$estudiante['uc_aprobadas']; ?> UC</strong>
                        <span>Aprobadas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon gold">📊</div>
                    <div class="stat-info">
                        <strong><?php echo $estudiante['promedio'] ?? '—'; ?></strong>
                        <span>Promedio General</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">✅</div>
                    <div class="stat-info">
                        <strong><?php echo count(array_filter($inscripciones, fn($i) => $i['estatus'] === 'Formalizada')); ?></strong>
                        <span>Materias Formalizadas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon <?php echo $uc_resguardadas > 0 ? 'red' : 'green'; ?>">💰</div>
                    <div class="stat-info">
                        <strong><?php echo $uc_resguardadas > 0 ? $uc_resguardadas . ' UC' : '—'; ?></strong>
                        <span><?php echo $uc_resguardadas > 0 ? 'Créditos Resguardados' : 'Sin saldos'; ?></span>
                    </div>
                </div>
            </div>

            <?php if ($uc_resguardadas > 0): ?>
            <div class="alert alert-warning">
                ⚠️ Posee <strong><?php echo $uc_resguardadas; ?> UC Resguardadas</strong> del lapso anterior.
                Este saldo se descontará automáticamente al inscribir nuevas materias.
            </div>
            <?php endif; ?>
        </section>

        <!-- INSCRIPCIÓN -->
        <section id="modulo-inscripcion" class="module-section" style="display:none;">
            <?php if (($sede['fase_actual']??1) != 2): ?>
                <div class="alert alert-info">El proceso de inscripción aún no ha iniciado en su sede.</div>
            <?php else: ?>
            <h3>📝 Portal de Inscripción Interactiva</h3>
            <p style="color:#666;margin-bottom:15px;">
                Seleccione las materias que desea cursar.
                <?php if ($uc_resguardadas > 0): ?>
                <br>✅ Tiene <strong><?php echo $uc_resguardadas; ?> UC Resguardadas</strong> a su favor.
                <?php endif; ?>
            </p>

            <?php if (count($oferta) === 0): ?>
                <div class="alert alert-info">No hay oferta académica disponible para esta sede.</div>
            <?php else: ?>
            <div class="form-grid-2">
                <?php foreach ($oferta as $mat): ?>
                <?php
                    $ya_inscrito = false;
                    foreach ($inscripciones as $insc) {
                        if ($insc['id'] == $mat['id'] || $insc['materia_codigo'] == $mat['materia_codigo']) {
                            if ($insc['estatus'] !== 'Eliminada') { $ya_inscrito = true; break; }
                        }
                    }
                    $disponible = $mat['inscritos'] < $mat['cupo_maximo'];
                ?>
                <div class="module-card" style="<?php echo $ya_inscrito ? 'opacity:0.6;' : ($disponible ? '' : 'opacity:0.4;'); ?>">
                    <div class="module-icon">📚</div>
                    <h3><?php echo h($mat['materia']); ?></h3>
                    <p>
                        Sección <?php echo h($mat['seccion']); ?> &middot; <?php echo $mat['uc']; ?> UC<br>
                        Prof: <?php echo h($mat['profesor'] ?? 'Por asignar'); ?><br>
                        Cupos: <?php echo $mat['inscritos']; ?>/<?php echo $mat['cupo_maximo']; ?>
                    </p>
                    <?php if ($ya_inscrito): ?>
                        <span class="badge badge-inscripcion" style="margin-top:8px;display:inline-block;">✅ Inscrito</span>
                    <?php elseif (!$disponible): ?>
                        <span class="badge badge-planar" style="margin-top:8px;display:inline-block;background:#e74c3c;color:white;">❌ Sección Agotada</span>
                    <?php else: ?>
                        <form method="POST" style="margin-top:8px;">
                            <input type="hidden" name="seccion_id" value="<?php echo $mat['id']; ?>">
                            <button type="submit" name="inscribir_materia" class="btn-action btn-green">➕ Seleccionar</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <h3 style="margin-top:25px;">Mis Materias Seleccionadas</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Materia</th><th>Sección</th><th>Profesor</th><th>UC</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($inscripciones as $insc): ?>
                    <tr>
                        <td><?php echo h($insc['materia']); ?></td>
                        <td><?php echo h($insc['seccion']); ?></td>
                        <td><?php echo h($insc['profesor'] ?? '—'); ?></td>
                        <td><?php echo $insc['uc']; ?> UC</td>
                        <td>
                            <?php if ($insc['estatus'] === 'Por Cancelar'): ?>
                                <span class="badge badge-planar">🟡 POR CANCELAR</span>
                            <?php elseif ($insc['estatus'] === 'Formalizada'): ?>
                                <span class="badge badge-inscripcion">🟢 FORMALIZADA</span>
                            <?php elseif ($insc['estatus'] === 'Eliminada'): ?>
                                <span class="badge" style="background:#e74c3c;color:white;">🔴 ELIMINADA</span>
                            <?php else: ?>
                                <span><?php echo h($insc['estatus']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:10px;color:#999;font-size:0.8rem;">
                ⚠️ Inscripción registrada. Para realizar cambios de materia, secciones o corrección de errores, diríjase a la taquilla de Secretaría.
            </p>
        </section>

        <!-- HORARIO -->
        <section id="modulo-horario" class="module-section" style="display:none;">
            <h3>📅 Mi Horario Consolidado</h3>
            <?php
            $dias_semana = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            $horario_grid = [];
            $formalizadas = array_filter($inscripciones, fn($i) => $i['estatus'] === 'Formalizada' || $i['estatus'] === 'Por Cancelar');
            ?>
            <table class="data-table">
                <thead>
                    <tr><th>Día</th><th>Materia</th><th>Horario</th><th>Aula</th><th>Profesor</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($formalizadas as $insc): ?>
                    <?php
                    $hors = $pdo->prepare("SELECT h.* FROM horarios h WHERE h.seccion_id = :sid");
                    $hors->execute([':sid' => $insc['id']]);
                    while ($h = $hors->fetch()):
                    ?>
                    <tr>
                        <td><strong><?php echo $dias_semana[$h['dia_semana']] ?? ''; ?></strong></td>
                        <td><?php echo h($insc['materia']); ?> (<?php echo h($insc['seccion']); ?>)</td>
                        <td><?php echo substr($h['hora_inicio'],0,5); ?> - <?php echo substr($h['hora_fin'],0,5); ?></td>
                        <td><?php echo h($insc['seccion']); ?></td>
                        <td><?php echo h($insc['profesor'] ?? '—'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endforeach; ?>
                    <?php if (count($formalizadas) === 0): ?>
                    <tr><td colspan="5" class="text-center">No tiene materias inscritas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- NOTAS -->
        <section id="modulo-notas" class="module-section" style="display:none;">
            <h3>📋 Historial de Calificaciones</h3>
            <?php if (count($notas) === 0): ?>
                <div class="alert alert-info">Aún no tiene calificaciones registradas.</div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Materia</th><th>Plan</th><th>UC</th><th>Nota</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($notas as $n): ?>
                    <tr>
                        <td><?php echo h($n['materia']); ?></td>
                        <td><?php echo h($n['plan_nombre']); ?></td>
                        <td><?php echo $n['uc']; ?> UC</td>
                        <td><strong style="font-size:1.2rem;"><?php echo $n['inasistencia'] ? 'N/S' : ($n['nota'] ?? '—'); ?></strong></td>
                        <td>
                            <?php if ($n['inasistencia']): ?>
                                <span class="badge badge-planar" style="background:#e74c3c;color:white;">Inasistente</span>
                            <?php elseif ($n['nota'] >= 14): ?>
                                <span class="badge badge-inscripcion">✅ Aprobado</span>
                            <?php elseif ($n['nota'] !== null): ?>
                                <span class="badge badge-planar" style="background:#e74c3c;color:white;">❌ Reprobado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </main>
</div>

</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
