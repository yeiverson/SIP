<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(7);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

iniciar_sesion_segura();

$nombre_director = $_SESSION['nombre_full'];
$mensaje = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $mensaje = alerta_error('Token de seguridad inválido.');
    } else {
        // 1. Cambiar fase de sede (Fase 1: Planificación / Admisiones, Fase 2: Inscripciones / Clases)
        if (isset($_POST['cambiar_fase'])) {
            $sede_id = (int)$_POST['sede_id'];
            $nueva_fase = (int)$_POST['nueva_fase'];
            try {
                $stmt = $pdo->prepare("UPDATE sedes SET fase_actual = :fase WHERE id = :id");
                $stmt->execute([':fase' => $nueva_fase, ':id' => $sede_id]);
                registrar_log($pdo, 'Cambiar fase sede', 'sedes', $sede_id, "Sede ID $sede_id -> Fase $nueva_fase");
                $mensaje = alerta_success("Fase operativa de la sede actualizada exitosamente a Fase $nueva_fase.");
            } catch (PDOException $e) {
                $mensaje = alerta_error("Error al actualizar la fase.");
            }
        }
        // 2. Crear nuevo Plan de Estudios (Especialización, Maestría, Doctorado)
        elseif (isset($_POST['crear_plan'])) {
            $nombre = trim($_POST['plan_nombre'] ?? '');
            $tipo = $_POST['plan_tipo'] ?? 'Maestria';
            $codigo = strtoupper(trim($_POST['plan_codigo'] ?? ''));
            $sedes_seleccionadas = $_POST['sedes'] ?? [];

            if ($nombre && $codigo && count($sedes_seleccionadas) > 0) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO plan_estudios (nombre, tipo, codigo, activo) VALUES (:n, :t, :c, true) RETURNING id");
                    $stmt->execute([':n' => $nombre, ':t' => $tipo, ':c' => $codigo]);
                    $plan_id = $stmt->fetchColumn();

                    $stmt_sede = $pdo->prepare("INSERT INTO plan_sede (plan_id, sede_id) VALUES (:pid, :sid) ON CONFLICT DO NOTHING");
                    foreach ($sedes_seleccionadas as $sid) {
                        $stmt_sede->execute([':pid' => $plan_id, ':sid' => (int)$sid]);
                    }
                    $pdo->commit();
                    registrar_log($pdo, 'Crear plan estudios', 'plan_estudios', $plan_id, "Plan: $nombre ($codigo)");
                    $mensaje = alerta_success("Plan de Estudios '$nombre' creado y habilitado en " . count($sedes_seleccionadas) . " sede(s).");
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $mensaje = ($e->getCode() == '23505') ? alerta_error("El código de programa '$codigo' ya existe.") : alerta_error("Error al crear plan: " . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error("Complete todos los campos del programa y seleccione al menos una sede habilitada.");
            }
        }
        // 3. Agregar asignatura al plan de estudios
        elseif (isset($_POST['agregar_asignatura'])) {
            $plan_id = (int)$_POST['plan_id'];
            $codigo = strtoupper(trim($_POST['asignatura_codigo'] ?? ''));
            $nombre_asig = trim($_POST['asignatura_nombre'] ?? '');
            $uc = (int)($_POST['asignatura_uc'] ?? 0);
            $semestre = (int)($_POST['semestre'] ?? 1);

            if ($codigo && $nombre_asig && $uc > 0 && $plan_id > 0) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO asignaturas (codigo, nombre, uc, activa) VALUES (:c, :n, :uc, true)
                                           ON CONFLICT (codigo) DO UPDATE SET nombre=EXCLUDED.nombre, uc=EXCLUDED.uc");
                    $stmt->execute([':c' => $codigo, ':n' => $nombre_asig, ':uc' => $uc]);

                    $stmtVinc = $pdo->prepare("INSERT INTO plan_asignaturas (plan_id, asignatura_codigo, semestre, obligatoria)
                                               VALUES (:pid, :cod, :sem, true) ON CONFLICT DO NOTHING");
                    $stmtVinc->execute([':pid' => $plan_id, ':cod' => $codigo, ':sem' => $semestre]);
                    $pdo->commit();

                    registrar_log($pdo, 'Vincular asignatura', 'plan_asignaturas', $plan_id, "Asignatura $codigo agregada al plan ID $plan_id");
                    $mensaje = alerta_success("Asignatura '$nombre_asig' ($codigo) integrada al plan de estudios.");
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $mensaje = alerta_error("Error al agregar asignatura: " . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error("Complete todos los datos de la asignatura.");
            }
        }
    }
}

// Consultas
$sedes = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM usuarios u WHERE u.sede_id = s.id AND u.rol_id = 6) as total_estudiantes
                       FROM sedes s ORDER BY s.nombre")->fetchAll();

$planes = $pdo->query("SELECT p.*,
                              (SELECT STRING_AGG(s.nombre, ', ') FROM plan_sede ps JOIN sedes s ON s.id = ps.sede_id WHERE ps.plan_id = p.id) as sedes_asignadas,
                              (SELECT COUNT(*) FROM plan_asignaturas pa WHERE pa.plan_id = p.id) as num_asignaturas,
                              (SELECT COALESCE(SUM(a.uc), 0) FROM plan_asignaturas pa JOIN asignaturas a ON a.codigo = pa.asignatura_codigo WHERE pa.plan_id = p.id) as total_uc
                       FROM plan_estudios p ORDER BY p.nombre")->fetchAll();

$asignaturas_todas = $pdo->query("SELECT * FROM asignaturas WHERE activa = true ORDER BY nombre")->fetchAll();

$totalAlumnosPostgrado = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol_id = 6")->fetchColumn();
$totalAspirantesPostgrado = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol_id = 5")->fetchColumn();

$titulo = 'Dirección de Postgrado e Investigación';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">Dirección General</span>
            </div>
            <p>Dir. <?php echo h($nombre_director); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Visión Ejecutiva</span></a>
            <a href="#modulo-fases" data-modulo="fases"><span>🔄 Control de Fases</span></a>
            <a href="#modulo-planes" data-modulo="planes"><span>📖 Programas y Mallas</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div>
                <h2>Dirección de Postgrado e Investigación UNEFA</h2>
                <p>Gobernanza Curricular y Control Estratégico de Períodos &middot; <?php echo fecha_hoy_formateada(); ?></p>
            </div>
            <div>
                <span class="badge badge-gold" style="font-size:0.85rem;padding:6px 14px;">🎖️ ALTA DIRECCIÓN</span>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <!-- KPI METRICS -->
        <div class="stats-grid">
            <div class="stat-card gold">
                <div class="stat-icon">🎓</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $totalAlumnosPostgrado; ?></span>
                    <span class="stat-lbl">Matrícula Regular</span>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">📑</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $totalAspirantesPostgrado; ?></span>
                    <span class="stat-lbl">Postulantes en Evaluación</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">📖</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($planes); ?></span>
                    <span class="stat-lbl">Programas Académicos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏛️</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($sedes); ?></span>
                    <span class="stat-lbl">Sedes Supervisadas</span>
                </div>
            </div>
        </div>

        <!-- MÓDULO: CONTROL DE FASES OPERATIVAS -->
        <section id="modulo-fases" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>🔄 Control de Fases Operativas por Sede</h3>
                </div>
                <div class="dashboard-card-body">
                    <p style="color:#666;font-size:0.85rem;margin-bottom:16px;">
                        <strong>Fase 1 (Planificación y Admisión):</strong> Los coordinadores estructuran la oferta y secretaría revisa expedientes de aspirantes.<br>
                        <strong>Fase 2 (Inscripciones y Clases):</strong> Se abre el portal de inscripción para estudiantes regulares y comienza la impartición de asignaturas.
                    </p>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Ubicación</th>
                                    <th>Estudiantes</th>
                                    <th>Fase Operativa Actual</th>
                                    <th>Conmutar Fase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sedes as $s): ?>
                                <tr>
                                    <td><strong><?php echo h($s['nombre']); ?> (<?php echo h($s['codigo']); ?>)</strong></td>
                                    <td><?php echo h($s['ubicacion']); ?></td>
                                    <td><span class="badge badge-navy"><?php echo $s['total_estudiantes']; ?> alumnos</span></td>
                                    <td>
                                        <span class="badge <?php echo $s['fase_actual'] == 1 ? 'badge-warning' : 'badge-success'; ?>" style="font-size:0.8rem;padding:6px 12px;">
                                            <?php echo $s['fase_actual'] == 1 ? '🟡 Fase 1: Planificación' : '🟢 Fase 2: Inscripción Abierta'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="cambiar_fase" value="1">
                                            <input type="hidden" name="sede_id" value="<?php echo $s['id']; ?>">
                                            <input type="hidden" name="nueva_fase" value="<?php echo $s['fase_actual'] == 1 ? 2 : 1; ?>">
                                            <button type="submit" class="btn-primary btn-sm" onclick="return confirm('¿Confirma conmutar la fase operativa para esta sede?')">
                                                Conmutar a Fase <?php echo $s['fase_actual'] == 1 ? '2 (Abrir Inscripciones)' : '1 (Cerrar Inscripciones)'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- MÓDULO: PROGRAMAS ACADÉMICOS Y MALLAS -->
        <section id="modulo-planes" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📖 Catálogo de Programas de Postgrado y Asignaturas</h3>
                    <button class="btn-primary btn-sm" onclick="toggleForm('form-nuevo-plan')">➕ Nuevo Programa Académico</button>
                </div>
                <div class="dashboard-card-body">
                    <!-- Formulario de nuevo plan -->
                    <div id="form-nuevo-plan" style="display:none;background:#f8fafc;padding:20px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:24px;">
                        <h4 style="margin-bottom:16px;color:#001a57;">Creación de Especialización, Maestría o Doctorado</h4>
                        <form method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="crear_plan" value="1">
                            <div class="form-grid-3">
                                <div class="input-group">
                                    <label>Nombre del Programa:</label>
                                    <input type="text" name="plan_nombre" placeholder="Ej: Doctorado en Innovación y Tecnología" required>
                                </div>
                                <div class="input-group">
                                    <label>Nivel Académico:</label>
                                    <select name="plan_tipo" required>
                                        <option value="Especializacion">Especialización</option>
                                        <option value="Maestria" selected>Maestría</option>
                                        <option value="Doctorado">Doctorado</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Código Identificador:</label>
                                    <input type="text" name="plan_codigo" placeholder="Ej: DIT-2026" required maxlength="20">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom:16px;">
                                <label>Sedes donde se impartirá el programa:</label>
                                <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:6px;">
                                    <?php foreach ($sedes as $s): ?>
                                    <label style="font-size:0.85rem;cursor:pointer;">
                                        <input type="checkbox" name="sedes[]" value="<?php echo $s['id']; ?>" checked> <?php echo h($s['nombre']); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div style="display:flex;gap:12px;">
                                <button type="submit" class="btn-primary">Guardar Programa</button>
                                <button type="button" class="btn-outline" onclick="toggleForm('form-nuevo-plan')">Cancelar</button>
                            </div>
                        </form>
                    </div>

                    <!-- Formulario para agregar asignaturas a mallas -->
                    <div style="background:#f8fafc;padding:20px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:24px;">
                        <h4 style="margin-bottom:14px;color:#001a57;">➕ Incorporar Asignatura a Malla Curricular</h4>
                        <form method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="agregar_asignatura" value="1">
                            <div class="form-grid-3">
                                <div class="input-group">
                                    <label>Programa Académico:</label>
                                    <select name="plan_id" required>
                                        <?php foreach ($planes as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo h($p['nombre']); ?> (<?php echo h($p['codigo']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Código de Asignatura:</label>
                                    <input type="text" name="asignatura_codigo" placeholder="Ej: INV701" required maxlength="15">
                                </div>
                                <div class="input-group">
                                    <label>Nombre de la Asignatura:</label>
                                    <input type="text" name="asignatura_nombre" placeholder="Ej: Epistemología y Métodos Cuantitativos" required>
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="input-group">
                                    <label>Unidades de Crédito (U.C.):</label>
                                    <input type="number" name="asignatura_uc" min="1" max="10" value="3" required>
                                </div>
                                <div class="input-group">
                                    <label>Término / Semestre:</label>
                                    <select name="semestre" required>
                                        <option value="1">Semestre I</option>
                                        <option value="2">Semestre II</option>
                                        <option value="3">Semestre III</option>
                                        <option value="4">Semestre IV</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-success">Integrar Asignatura al Pensum</button>
                        </form>
                    </div>

                    <!-- Listado de programas -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Programa</th>
                                    <th>Nivel</th>
                                    <th>Asignaturas</th>
                                    <th>Total UC Requeridas</th>
                                    <th>Sedes Habilitadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($planes as $pl): ?>
                                <tr>
                                    <td><strong><?php echo h($pl['codigo']); ?></strong></td>
                                    <td><strong><?php echo h($pl['nombre']); ?></strong></td>
                                    <td><span class="badge badge-navy"><?php echo h($pl['tipo']); ?></span></td>
                                    <td><?php echo $pl['num_asignaturas']; ?> asignaturas</td>
                                    <td><strong><?php echo $pl['total_uc']; ?> UC</strong></td>
                                    <td><small><?php echo h($pl['sedes_asignadas'] ?: 'Todas las sedes'); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
