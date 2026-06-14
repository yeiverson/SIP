<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(7);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$nombre_director = $_SESSION['nombre_full'];
$mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cambiar fase de sede
    if (isset($_POST['cambiar_fase'])) {
        $sede_id = (int)$_POST['sede_id'];
        $nueva_fase = (int)$_POST['nueva_fase'];
        try {
            $stmt = $pdo->prepare("UPDATE sedes SET fase_actual = :fase WHERE id = :id");
            $stmt->execute([':fase' => $nueva_fase, ':id' => $sede_id]);
            registrar_log($pdo, 'Cambiar fase sede', 'sedes', $sede_id, "Sede ID $sede_id -> Fase $nueva_fase");
            $mensaje = alerta_success("Fase cambiada exitosamente.");
        } catch (PDOException $e) {
            $mensaje = alerta_error("Error al cambiar fase.");
        }
    }
    // Crear plan de estudios
    elseif (isset($_POST['crear_plan'])) {
        $nombre = trim($_POST['plan_nombre']);
        $tipo = $_POST['plan_tipo'];
        $codigo = strtoupper(trim($_POST['plan_codigo']));
        $sedes_seleccionadas = $_POST['sedes'] ?? [];

        if ($nombre && $codigo && count($sedes_seleccionadas) > 0) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO plan_estudios (nombre, tipo, codigo) VALUES (:n, :t, :c)");
                $stmt->execute([':n' => $nombre, ':t' => $tipo, ':c' => $codigo]);
                $plan_id = $pdo->lastInsertId();

                $stmt_sede = $pdo->prepare("INSERT INTO plan_sede (plan_id, sede_id) VALUES (:pid, :sid)");
                foreach ($sedes_seleccionadas as $sid) {
                    $stmt_sede->execute([':pid' => $plan_id, ':sid' => (int)$sid]);
                }
                $pdo->commit();
                registrar_log($pdo, 'Crear plan estudios', 'plan_estudios', $plan_id);
                $mensaje = alerta_success("Plan '$nombre' creado y vinculado a " . count($sedes_seleccionadas) . " sede(s).");
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = $e->getCode() == '23505' ? alerta_error("El código '$codigo' ya existe.") : alerta_error("Error al crear plan.");
            }
        } else {
            $mensaje = alerta_error("Complete todos los campos y seleccione al menos una sede.");
        }
    }
    // Agregar asignatura al plan
    elseif (isset($_POST['agregar_asignatura'])) {
        $plan_id = (int)$_POST['plan_id'];
        $codigo = strtoupper(trim($_POST['asignatura_codigo']));
        $nombre_asig = trim($_POST['asignatura_nombre']);
        $uc = (int)$_POST['asignatura_uc'];
        $semestre = (int)$_POST['semestre'];

        if ($codigo && $nombre_asig && $uc > 0) {
            try {
                $pdo->beginTransaction();
                // Insertar o ignorar asignatura
                $stmt = $pdo->prepare("INSERT INTO asignaturas (codigo, nombre, uc) VALUES (:c, :n, :uc) ON CONFLICT (codigo) DO UPDATE SET nombre=EXCLUDED.nombre, uc=EXCLUDED.uc");
                $stmt->execute([':c' => $codigo, ':n' => $nombre_asig, ':uc' => $uc]);
                // Vincular al plan
                $stmt = $pdo->prepare("INSERT INTO plan_asignaturas (plan_id, asignatura_codigo, semestre) VALUES (:pid, :cod, :sem) ON CONFLICT DO NOTHING");
                $stmt->execute([':pid' => $plan_id, ':cod' => $codigo, ':sem' => $semestre]);
                $pdo->commit();
                $mensaje = alerta_success("Asignatura '$nombre_asig' agregada al plan.");
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = alerta_error("Error al agregar asignatura.");
            }
        }
    }
}

// Sedes
$sedes = $pdo->query("SELECT * FROM sedes ORDER BY nombre")->fetchAll();

// Planes con sedes
$planes = $pdo->query("SELECT p.*, (SELECT STRING_AGG(s.nombre, ', ') FROM plan_sede ps JOIN sedes s ON s.id = ps.sede_id WHERE ps.plan_id = p.id) as sedes_asignadas,
                        (SELECT COUNT(*) FROM plan_asignaturas pa WHERE pa.plan_id = p.id) as num_asignaturas
                        FROM plan_estudios p ORDER BY p.nombre")->fetchAll();

// Asignaturas disponibles
$asignaturas = $pdo->query("SELECT * FROM asignaturas WHERE activa = true ORDER BY nombre")->fetchAll();
$asignaturas_no_en_plan = []; // Se usa en el modal

$titulo = 'Panel Director';
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
            <p>Director: <?php echo h($nombre_director); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Inicio</span></a>
            <a href="#modulo-planes" data-modulo="planes"><span>📚 Planes de Estudio</span></a>
            <a href="#modulo-fases" data-modulo="fases"><span>⚙️ Control de Fases</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Panel del Director de Postgrado</h2>
            <p><?php echo fecha_hoy_formateada(); ?> &middot; Control Estratégico Global</p>
        </header>

        <?php echo $mensaje; ?>

        <!-- INICIO -->
        <section id="modulo-inicio" class="module-section">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:15px;">
                <div class="form-section">
                    <h3>📚 <?php echo count($planes); ?></h3>
                    <p>Planes de Estudio</p>
                </div>
                <div class="form-section">
                    <h3>🌐 <?php echo count($sedes); ?></h3>
                    <p>Sedes / Núcleos</p>
                </div>
                <div class="form-section">
                    <h3>⚙️ <?php echo count(array_filter($sedes, fn($s) => $s['fase_actual'] == 2)); ?></h3>
                    <p>Sedes en Fase 2 (Inscripciones)</p>
                </div>
            </div>
        </section>

        <!-- PLANES -->
        <section id="modulo-planes" class="module-section" style="display:none;">
            <h3>📚 Gestión Curricular</h3>

            <div class="form-section">
                <h4>Crear Nuevo Plan de Estudios</h4>
                <form method="POST">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label>Nombre del Programa <span class="req">*</span></label>
                            <input type="text" name="plan_nombre" placeholder="Ej: Maestría en Educación Superior" required>
                        </div>
                        <div class="form-group">
                            <label>Tipo <span class="req">*</span></label>
                            <select name="plan_tipo" required>
                                <option value="Especializacion">Especialización</option>
                                <option value="Maestria" selected>Maestría</option>
                                <option value="Doctorado">Doctorado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Código <span class="req">*</span></label>
                            <input type="text" name="plan_codigo" placeholder="Ej: MES-2026" required maxlength="20">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar Sedes Autorizadas <span class="req">*</span></label>
                        <div style="display:flex;gap:15px;flex-wrap:wrap;margin-top:5px;">
                            <?php foreach ($sedes as $s): ?>
                            <label style="font-weight:400;font-size:0.85rem;">
                                <input type="checkbox" name="sedes[]" value="<?php echo $s['id']; ?>">
                                <?php echo h($s['nombre']); ?> (<?php echo h($s['codigo']); ?>)
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" name="crear_plan" class="btn-submit">Crear Plan de Estudios</button>
                </form>
            </div>

            <div class="form-section" style="margin-top:20px;">
                <h4>Agregar Asignatura a un Plan</h4>
                <form method="POST">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label>Plan de Estudios</label>
                            <select name="plan_id" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($planes as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo h($p['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Semestre</label>
                            <select name="semestre" required>
                                <?php for ($i=1; $i<=4; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?>° Semestre</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label>Código</label>
                            <input type="text" name="asignatura_codigo" placeholder="Ej: MAT101" required maxlength="20">
                        </div>
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="asignatura_nombre" placeholder="Nombre de la materia" required>
                        </div>
                        <div class="form-group">
                            <label>UC</label>
                            <input type="number" name="asignatura_uc" min="1" max="10" required>
                        </div>
                    </div>
                    <button type="submit" name="agregar_asignatura" class="btn-submit">Agregar Asignatura al Plan</button>
                </form>
            </div>

            <h4 style="margin-top:25px;">Planes de Estudio Registrados</h4>
            <table class="data-table">
                <thead>
                    <tr><th>Código</th><th>Nombre</th><th>Tipo</th><th>Asignaturas</th><th>Sedes</th><th>Activo</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($planes as $p): ?>
                    <tr>
                        <td><strong><?php echo h($p['codigo']); ?></strong></td>
                        <td><?php echo h($p['nombre']); ?></td>
                        <td><?php echo h($p['tipo']); ?></td>
                        <td><?php echo $p['num_asignaturas']; ?></td>
                        <td><?php echo h($p['sedes_asignadas'] ?? 'Ninguna'); ?></td>
                        <td><?php echo $p['activo'] ? '✅' : '❌'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- FASES -->
        <section id="modulo-fases" class="module-section" style="display:none;">
            <h3>⚙️ Control de Fases por Sede</h3>
            <p style="color:#666;margin-bottom:15px;">
                Fase 1: Planificación Académica (Coordinadores crean oferta)<br>
                Fase 2: Inscripciones Abiertas (Estudiantes se inscriben)
            </p>

            <table class="data-table">
                <thead>
                    <tr><th>Sede</th><th>Código</th><th>Fase Actual</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($sedes as $s): ?>
                    <tr>
                        <td><strong><?php echo h($s['nombre']); ?></strong></td>
                        <td><?php echo h($s['codigo']); ?></td>
                        <td>
                            <span class="badge <?php echo $s['fase_actual']==1 ? 'badge-planar' : 'badge-inscripcion'; ?>">
                                <?php echo $s['fase_actual'] == 1 ? 'Fase 1: Planificación' : 'Fase 2: Inscripciones'; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="sede_id" value="<?php echo $s['id']; ?>">
                                <?php if ($s['fase_actual'] == 1): ?>
                                <input type="hidden" name="nueva_fase" value="2">
                                <button type="submit" name="cambiar_fase" class="btn-action btn-green"
                                        onclick="return confirm('¿Activar Fase 2 (Inscripciones) para <?php echo h($s['nombre']); ?>?')">
                                    🔓 Abrir Inscripciones (Fase 2)
                                </button>
                                <?php else: ?>
                                <input type="hidden" name="nueva_fase" value="1">
                                <button type="submit" name="cambiar_fase" class="btn-action btn-orange"
                                        onclick="return confirm('¿Volver a Fase 1 (Planificación) para <?php echo h($s['nombre']); ?>?')">
                                    🔒 Cerrar Inscripciones (Fase 1)
                                </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
