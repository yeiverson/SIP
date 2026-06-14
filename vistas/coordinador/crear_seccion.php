<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(2);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$sede_id = $_SESSION['sede_id'];
$mensaje = '';

// Obtener sede
$sede_info = $pdo->prepare("SELECT * FROM sedes WHERE id = :id");
$sede_info->execute([':id' => $sede_id]);
$sede = $sede_info->fetch();

if (($sede['fase_actual'] ?? 1) != 1) {
    die(alerta_error('No puede crear secciones durante la Fase 2 (Inscripciones).'));
}

// Procesar creación de sección + asignación de profesor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_seccion'])) {
    $asignatura_codigo = strtoupper(trim($_POST['asignatura_codigo']));
    $seccion = strtoupper(trim($_POST['seccion']));
    $profesor_id = !empty($_POST['profesor_id']) ? (int)$_POST['profesor_id'] : null;
    $cupo_maximo = (int)($_POST['cupo_maximo'] ?? 25);
    $aula = trim($_POST['aula'] ?? '');
    $plan_id = (int)($_POST['plan_id'] ?? 0);

    // Validar choque de horarios
    $dia = (int)$_POST['dia_semana'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    if ($profesor_id && $dia && $hora_inicio && $hora_fin) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM horarios h
                               JOIN secciones s ON s.id = h.seccion_id
                               WHERE s.profesor_id = :prof
                               AND h.dia_semana = :dia
                               AND h.hora_inicio < :hf
                               AND h.hora_fin > :hi
                               AND s.activa = true");
        $stmt->execute([':prof' => $profesor_id, ':dia' => $dia, ':hf' => $hora_fin, ':hi' => $hora_inicio]);
        $choque = $stmt->fetch();

        if ($choque['total'] > 0) {
            $mensaje = alerta_error('⚠️ El Docente ya se encuentra ocupado en este horario.');
        }
    }

    if (!$mensaje) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO secciones (plan_id, asignatura_codigo, seccion, profesor_id, sede_id, cupo_maximo, aula, periodo)
                    VALUES (:plan, :asig, :sec, :prof, :sede, :cupo, :aula, :periodo)");
            $periodo = date('Y') . '-' . (date('m') >= 6 ? 'II' : 'I');
            $stmt->execute([
                ':plan' => $plan_id,
                ':asig' => $asignatura_codigo,
                ':sec'  => $seccion,
                ':prof' => $profesor_id,
                ':sede' => $sede_id,
                ':cupo' => $cupo_maximo,
                ':aula' => $aula,
                ':periodo' => $periodo,
            ]);
            $seccion_id = $pdo->lastInsertId();

            // Crear horario
            if ($dia && $hora_inicio && $hora_fin) {
                $stmt = $pdo->prepare("INSERT INTO horarios (seccion_id, dia_semana, hora_inicio, hora_fin) VALUES (:sid, :dia, :hi, :hf)");
                $stmt->execute([':sid' => $seccion_id, ':dia' => $dia, ':hi' => $hora_inicio, ':hf' => $hora_fin]);
            }

            $pdo->commit();
            registrar_log($pdo, 'Crear sección', 'secciones', $seccion_id);
            $mensaje = alerta_success("Sección $seccion creada exitosamente.");
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = alerta_error("Error al crear sección: " . $e->getMessage());
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_profesor'])) {
    $seccion_id = (int)$_POST['seccion_id'];
    $profesor_id = (int)$_POST['profesor_id'];

    try {
        $stmt = $pdo->prepare("UPDATE secciones SET profesor_id = :prof WHERE id = :id");
        $stmt->execute([':prof' => $profesor_id, ':id' => $seccion_id]);
        registrar_log($pdo, 'Asignar profesor a sección', 'secciones', $seccion_id);
        $mensaje = alerta_success('Profesor asignado a la sección.');
    } catch (PDOException $e) {
        $mensaje = alerta_error('Error al asignar profesor.');
    }
}

// Datos para los selects
$planes = $pdo->query("SELECT p.* FROM plan_estudios p
                        JOIN plan_sede ps ON ps.plan_id = p.id
                        WHERE ps.sede_id = $sede_id AND p.activo = true
                        ORDER BY p.nombre")->fetchAll();

$asignaturas = $pdo->query("SELECT a.* FROM asignaturas a WHERE a.activa = true ORDER BY a.nombre")->fetchAll();

$profesores = $pdo->prepare("SELECT id, tipo_cedula, numero_documento, nombres, apellidos, email FROM usuarios WHERE rol_id = 3 ORDER BY apellidos");
$profesores->execute();
$profesores_lista = $profesores->fetchAll();

$secciones = $pdo->prepare("SELECT sec.*, asig.nombre as materia_nombre, asig.uc, pl.nombre as plan_nombre,
                            CONCAT(p.tipo_cedula,'-',p.numero_documento,' | ',p.nombres,' ',p.apellidos) as profesor_nombre
                            FROM secciones sec
                            JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                            JOIN plan_estudios pl ON pl.id = sec.plan_id
                            LEFT JOIN usuarios p ON p.id = sec.profesor_id
                            WHERE sec.sede_id = :sede
                            ORDER BY sec.created_at DESC");
$secciones->execute([':sede' => $sede_id]);

$titulo = 'Crear Secciones';
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
            <p>Coord: <?php echo h($_SESSION['nombre_full']); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php">🏠 Inicio</a>
            <a href="crear_seccion.php" class="active">➕ Crear Secciones</a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Creación de Secciones y Asignación de Horarios</h2>
            <p>Sede: <strong><?php echo h($sede['nombre'] ?? 'N/A'); ?></strong></p>
        </header>

        <?php echo $mensaje; ?>

        <div class="form-section">
            <h3>➕ Nueva Sección</h3>
            <form method="POST">
                <div class="form-grid-2">
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
                        <label>Asignatura</label>
                        <select name="asignatura_codigo" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($asignaturas as $a): ?>
                            <option value="<?php echo h($a['codigo']); ?>"><?php echo h($a['codigo'] . ' - ' . $a['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sección</label>
                        <input type="text" name="seccion" placeholder="Ej: A, B, Única" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>Cupo Máximo</label>
                        <input type="number" name="cupo_maximo" value="25" min="1" max="50">
                    </div>
                    <div class="form-group">
                        <label>Aula</label>
                        <input type="text" name="aula" placeholder="Ej: Aula 304">
                    </div>
                    <div class="form-group">
                        <label>Profesor</label>
                        <select name="profesor_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($profesores_lista as $p): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo h($p['tipo_cedula'].'-'.$p['numero_documento'].' | '.$p['nombres'].' '.$p['apellidos']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <h4 style="margin:15px 0 10px;">Horario</h4>
                <div class="form-grid-3">
                    <div class="form-group">
                        <label>Día</label>
                        <select name="dia_semana" required>
                            <option value="">Seleccione</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hora Inicio</label>
                        <input type="time" name="hora_inicio" required>
                    </div>
                    <div class="form-group">
                        <label>Hora Fin</label>
                        <input type="time" name="hora_fin" required>
                    </div>
                </div>
                <button type="submit" name="crear_seccion" class="btn-submit">Crear Sección</button>
            </form>
        </div>

        <div class="table-section" style="margin-top:20px;">
            <h3>Secciones Creadas</h3>
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Plan</th><th>Asignatura</th><th>Sección</th><th>Profesor</th><th>Cupos</th></tr>
                </thead>
                <tbody>
                    <?php while ($sec = $secciones->fetch()): ?>
                    <tr>
                        <td><?php echo $sec['id']; ?></td>
                        <td><?php echo h($sec['plan_nombre']); ?></td>
                        <td><?php echo h($sec['materia_nombre']); ?></td>
                        <td><strong><?php echo h($sec['seccion']); ?></strong></td>
                        <td><?php echo h($sec['profesor_nombre'] ?? '—'); ?></td>
                        <td><?php echo $sec['cupo_actual']; ?>/<?php echo $sec['cupo_maximo']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
