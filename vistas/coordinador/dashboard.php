<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(2);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

iniciar_sesion_segura();

$nombre_coord = $_SESSION['nombre_full'];
$sede_id      = (int)($_SESSION['sede_id'] ?? 0);
$mensaje      = '';

// Obtener datos de la sede
$sede_info = null;
if ($sede_id > 0) {
    $sede = $pdo->prepare("SELECT * FROM sedes WHERE id = :id");
    $sede->execute([':id' => $sede_id]);
    $sede_info = $sede->fetch();
}

// Procesar solicitud urgente de docente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $mensaje = alerta_error('Token de seguridad inválido.');
    } else {
        if (isset($_POST['solicitar_docente'])) {
            $tipo_doc = strtoupper(trim($_POST['tipo_documento'] ?? 'V'));
            $num_doc = trim($_POST['numero_documento'] ?? '');
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $nacionalidad = trim($_POST['nacionalidad'] ?? 'Venezolana');

            if ($tipo_doc && $num_doc && $nombres && $apellidos && $email) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO solicitudes_docentes (coordinador_id, sede_id, tipo_documento, numero_documento, nombres, apellidos, email, nacionalidad, estatus)
                            VALUES (:coord, :sede, :tipo, :doc, :nom, :ape, :email, :nac, 'Pendiente') RETURNING id");
                    $stmt->execute([
                        ':coord' => $_SESSION['usuario_id'],
                        ':sede'  => $sede_id ?: null,
                        ':tipo'  => $tipo_doc,
                        ':doc'   => $num_doc,
                        ':nom'   => $nombres,
                        ':ape'   => $apellidos,
                        ':email' => $email,
                        ':nac'   => $nacionalidad,
                    ]);
                    $sol_id = $stmt->fetchColumn();
                    registrar_log($pdo, 'Solicitar registro docente', 'solicitudes_docentes', $sol_id, "Coordinador solicitó docente $nombres $apellidos");
                    $mensaje = alerta_success("Solicitud enviada a la Administración Central para el docente $nombres $apellidos.");
                } catch (PDOException $e) {
                    $mensaje = alerta_error("Error al enviar solicitud: " . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error("Complete todos los campos obligatorios para emitir la solicitud.");
            }
        }
    }
}

// Obtener profesores disponibles
$profesores = $pdo->prepare("SELECT id, tipo_cedula, numero_documento, nombres, apellidos, email FROM usuarios WHERE rol_id = 3 AND (sede_id = :sede OR sede_id IS NULL) ORDER BY apellidos, nombres");
$profesores->execute([':sede' => $sede_id]);
$profesores = $profesores->fetchAll();

// Obtener secciones de la sede
$secciones = $pdo->prepare("SELECT sec.*, asig.nombre as materia_nombre, asig.uc, pl.nombre as plan_nombre,
                            (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = sec.id AND i.estatus != 'Eliminada') as inscritos
                            FROM secciones sec
                            JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                            JOIN plan_estudios pl ON pl.id = sec.plan_id
                            WHERE (:sede = 0 OR sec.sede_id = :sede)
                            ORDER BY sec.created_at DESC");
$secciones->execute([':sede' => $sede_id]);
$secciones = $secciones->fetchAll();

// Solicitudes emitidas por este coordinador
$stmtMisSol = $pdo->prepare("SELECT * FROM solicitudes_docentes WHERE coordinador_id = :cid ORDER BY created_at DESC LIMIT 20");
$stmtMisSol->execute([':cid' => $_SESSION['usuario_id']]);
$mis_solicitudes = $stmtMisSol->fetchAll();

$titulo = 'Panel de Coordinación Académica';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">Coordinación</span>
            </div>
            <p>Coord. <?php echo h($nombre_coord); ?></p>
            <p><small><?php echo h($sede_info['nombre'] ?? 'Sede Central'); ?></small></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Inicio</span></a>
            <a href="crear_seccion.php"><span>➕ Crear Secciones</span></a>
            <a href="#modulo-oferta" data-modulo="oferta"><span>📅 Oferta y Horarios</span></a>
            <a href="#modulo-docentes" data-modulo="docentes"><span>👨‍🏫 Requerir Docente</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div>
                <h2>Coordinación de Programas de Postgrado</h2>
                <p>Sede: <strong><?php echo h($sede_info['nombre'] ?? 'Central'); ?></strong> &middot; <?php echo fecha_hoy_formateada(); ?></p>
            </div>
            <div>
                <a href="crear_seccion.php" class="btn-primary btn-sm">➕ Nueva Sección y Horario</a>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <!-- KPI STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($secciones); ?></span>
                    <span class="stat-lbl">Secciones Ofertadas</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($profesores); ?></span>
                    <span class="stat-lbl">Docentes en Nómina</span>
                </div>
            </div>
            <div class="stat-card gold">
                <div class="stat-icon">🏛️</div>
                <div class="stat-details">
                    <span class="stat-val">Fase <?php echo $sede_info['fase_actual'] ?? 1; ?></span>
                    <span class="stat-lbl"><?php echo ($sede_info['fase_actual'] ?? 1) == 1 ? 'Planificación' : 'Inscripción Abierta'; ?></span>
                </div>
            </div>
        </div>

        <!-- MÓDULO: OFERTA ACADÉMICA Y HORARIOS -->
        <section id="modulo-oferta" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📅 Secciones Planificadas y Disponibilidad de Cupos</h3>
                    <a href="crear_seccion.php" class="btn-outline btn-sm">Abrir Creador de Sección →</a>
                </div>
                <div class="dashboard-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Programa</th>
                                    <th>Asignatura</th>
                                    <th>Sección</th>
                                    <th>Profesor</th>
                                    <th>U.C.</th>
                                    <th>Cupos</th>
                                    <th>Horario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($secciones) === 0): ?>
                                <tr><td colspan="7" class="text-center">No hay secciones registradas para este período. <a href="crear_seccion.php">Crear sección ahora &rarr;</a></td></tr>
                                <?php else: ?>
                                <?php foreach ($secciones as $sec): ?>
                                <tr>
                                    <td><small><?php echo h($sec['plan_nombre']); ?></small></td>
                                    <td><strong><?php echo h($sec['materia_nombre']); ?></strong></td>
                                    <td>Sec. <?php echo h($sec['seccion']); ?></td>
                                    <td>
                                        <?php
                                        $stmtP = $pdo->prepare("SELECT nombres, apellidos, tipo_cedula, numero_documento FROM usuarios WHERE id = :id");
                                        $stmtP->execute([':id' => $sec['profesor_id']]);
                                        $prof = $stmtP->fetch();
                                        echo $prof ? h($prof['tipo_cedula'] . '-' . $prof['numero_documento'] . ' | ' . $prof['nombres'] . ' ' . $prof['apellidos']) : '<span style="color:#94a3b8;">Por designar</span>';
                                        ?>
                                    </td>
                                    <td><?php echo $sec['uc']; ?> UC</td>
                                    <td>
                                        <strong><?php echo $sec['inscritos']; ?></strong> / <?php echo $sec['cupo_maximo']; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $stmtH = $pdo->prepare("SELECT * FROM horarios WHERE seccion_id = :sid");
                                        $stmtH->execute([':sid' => $sec['id']]);
                                        $dias = ['', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                                        $horarios = $stmtH->fetchAll();
                                        if (count($horarios) === 0) {
                                            echo '<span style="color:#999;">Sin horario</span>';
                                        } else {
                                            foreach ($horarios as $h) {
                                                echo '<span class="badge badge-navy" style="margin-right:2px;">' . ($dias[$h['dia_semana']] ?? '') . ' ' . substr($h['hora_inicio'], 0, 5) . '-' . substr($h['hora_fin'], 0, 5) . '</span> ';
                                            }
                                        }
                                        ?>
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

        <!-- MÓDULO: SOLICITAR NUEVO DOCENTE -->
        <section id="modulo-docentes" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📨 Requerimiento Urgente de Incorporación Docente</h3>
                </div>
                <div class="dashboard-card-body">
                    <p style="color:#666;font-size:0.85rem;margin-bottom:16px;">
                        Si requiere incorporar un profesional calificado para dictar asignaturas en su programa, envíe esta requisición a la Administración Central para su alta inmediata.
                    </p>
                    <form method="POST" style="background:#f8fafc;padding:20px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:24px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="solicitar_docente" value="1">
                        <div class="form-grid-3">
                            <div class="input-group">
                                <label>Tipo de Documento:</label>
                                <select name="tipo_documento" required>
                                    <option value="V">V - Venezolano</option>
                                    <option value="E">E - Extranjero</option>
                                    <option value="P">P - Pasaporte</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Número de Cédula / Pasaporte:</label>
                                <input type="text" name="numero_documento" placeholder="Ej: 12345678" required>
                            </div>
                            <div class="input-group">
                                <label>Nacionalidad:</label>
                                <input type="text" name="nacionalidad" value="Venezolana" required>
                            </div>
                        </div>
                        <div class="form-grid-3">
                            <div class="input-group">
                                <label>Nombres del Docente:</label>
                                <input type="text" name="nombres" placeholder="Nombres" required>
                            </div>
                            <div class="input-group">
                                <label>Apellidos del Docente:</label>
                                <input type="text" name="apellidos" placeholder="Apellidos" required>
                            </div>
                            <div class="input-group">
                                <label>Correo Institucional / Personal:</label>
                                <input type="email" name="email" placeholder="docente@ejemplo.com" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">📨 Transmitir Requerimiento a Administración</button>
                    </form>

                    <h4>Mis Requerimientos Emitidos</h4>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Documento</th>
                                    <th>Docente</th>
                                    <th>Correo</th>
                                    <th>Estatus Solicitud</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($mis_solicitudes) === 0): ?>
                                <tr><td colspan="5" class="text-center">No ha realizado requerimientos de docentes recientemente.</td></tr>
                                <?php else: ?>
                                <?php foreach ($mis_solicitudes as $ms): ?>
                                <tr>
                                    <td><?php echo formatear_fecha($ms['created_at']); ?></td>
                                    <td><strong><?php echo h($ms['tipo_documento'] . '-' . $ms['numero_documento']); ?></strong></td>
                                    <td><?php echo h($ms['nombres'] . ' ' . $ms['apellidos']); ?></td>
                                    <td><?php echo h($ms['email']); ?></td>
                                    <td>
                                        <?php if ($ms['estatus'] === 'Aprobado'): ?>
                                            <span class="badge badge-success">🟢 Aprobado</span>
                                        <?php elseif ($ms['estatus'] === 'Rechazado'): ?>
                                            <span class="badge badge-danger">🔴 Rechazado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">🟡 Pendiente por Admin</span>
                                        <?php endif; ?>
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
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
