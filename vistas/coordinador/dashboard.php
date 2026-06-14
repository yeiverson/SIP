<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(2);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$nombre_coord = $_SESSION['nombre_full'];
$sede_id = $_SESSION['sede_id'];
$mensaje = '';

// Obtener datos de la sede
$sede = $pdo->prepare("SELECT * FROM sedes WHERE id = :id");
$sede->execute([':id' => $sede_id]);
$sede_info = $sede->fetch();

// Procesar solicitud urgente de docente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_docente'])) {
    $tipo_doc = $_POST['tipo_documento'];
    $num_doc = trim($_POST['numero_documento']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $nacionalidad = trim($_POST['nacionalidad']);

    if ($tipo_doc && $num_doc && $nombres && $apellidos && $email) {
        try {
            $stmt = $pdo->prepare("INSERT INTO solicitudes_docentes (coordinador_id, sede_id, tipo_documento, numero_documento, nombres, apellidos, email, nacionalidad)
                    VALUES (:coord, :sede, :tipo, :doc, :nom, :ape, :email, :nac)");
            $stmt->execute([
                ':coord' => $_SESSION['usuario_id'],
                ':sede'  => $sede_id,
                ':tipo'  => $tipo_doc,
                ':doc'   => $num_doc,
                ':nom'   => $nombres,
                ':ape'   => $apellidos,
                ':email' => $email,
                ':nac'   => $nacionalidad,
            ]);
            registrar_log($pdo, 'Solicitar registro docente', 'solicitudes_docentes', $pdo->lastInsertId());
            $mensaje = alerta_success("Solicitud enviada al Administrador para $nombres $apellidos.");
        } catch (PDOException $e) {
            $mensaje = alerta_error("Error al enviar solicitud.");
        }
    } else {
        $mensaje = alerta_error("Complete todos los campos obligatorios.");
    }
}

// Obtener profesores disponibles
$profesores = $pdo->prepare("SELECT id, tipo_cedula, numero_documento, nombres, apellidos, email FROM usuarios WHERE rol_id = 3 AND (sede_id = :sede OR sede_id IS NULL)");
$profesores->execute([':sede' => $sede_id]);
$profesores = $profesores->fetchAll();

// Obtener secciones de la sede
$secciones = $pdo->prepare("SELECT sec.*, asig.nombre as materia_nombre, asig.uc,
                            (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = sec.id AND i.estatus != 'Eliminada') as inscritos
                            FROM secciones sec
                            JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                            WHERE sec.sede_id = :sede
                            ORDER BY sec.created_at DESC");
$secciones->execute([':sede' => $sede_id]);
$secciones = $secciones->fetchAll();

$titulo = 'Panel Coordinador';
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
            <p>Coord: <?php echo h($nombre_coord); ?></p>
            <p><small><?php echo h($sede_info['nombre'] ?? 'Sin sede'); ?></small></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Inicio</span></a>
            <a href="#modulo-oferta" data-modulo="oferta"><span>📅 Planificar Oferta</span></a>
            <a href="#modulo-solicitudes" data-modulo="solicitudes"><span>📨 Solicitar Docente</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Panel del Coordinador</h2>
            <p>Sede: <strong><?php echo h($sede_info['nombre'] ?? 'No asignada'); ?></strong>
               &middot; Fase: <span class="badge <?php echo ($sede_info['fase_actual']??1)==1?'badge-planar':'badge-inscripcion'; ?>">
               <?php echo ($sede_info['fase_actual']??1)==1 ? 'Fase 1: Planificación' : 'Fase 2: Inscripciones'; ?></span>
            </p>
        </header>

        <?php echo $mensaje; ?>

        <!-- MÓDULO: OFERTA ACADÉMICA -->
        <section id="modulo-oferta" class="module-section">
            <h3>📅 Planificador de Oferta y Horarios</h3>

            <div class="form-section">
                <h4>Secciones actuales</h4>
                <table class="data-table">
                    <thead>
                        <tr><th>Materia</th><th>Sección</th><th>Profesor</th><th>UC</th><th>Cupos</th><th>Inscritos</th><th>Día/Hora</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($secciones) === 0): ?>
                        <tr><td colspan="7" class="text-center">No hay secciones creadas. <?php if (($sede_info['fase_actual']??1)==1): ?>
                            <a href="crear_seccion.php">Crear primera sección →</a>
                        <?php endif; ?></td></tr>
                        <?php else: ?>
                        <?php foreach ($secciones as $sec): ?>
                        <tr>
                            <td><?php echo h($sec['materia_nombre']); ?></td>
                            <td><?php echo h($sec['seccion']); ?></td>
                            <td><?php
                                $prof = $pdo->prepare("SELECT nombres, apellidos, tipo_cedula, numero_documento FROM usuarios WHERE id = :id");
                                $prof->execute([':id' => $sec['profesor_id']]);
                                $p = $prof->fetch();
                                echo $p ? h($p['tipo_cedula'].'-'.$p['numero_documento'].' | '.$p['nombres'].' '.$p['apellidos']) : '—';
                            ?></td>
                            <td><?php echo $sec['uc']; ?> UC</td>
                            <td><?php echo $sec['cupo_maximo']; ?></td>
                            <td><?php echo $sec['inscritos']; ?></td>
                            <td><?php
                                $hors = $pdo->prepare("SELECT * FROM horarios WHERE seccion_id = :sid");
                                $hors->execute([':sid' => $sec['id']]);
                                $dias = ['','Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
                                while ($h = $hors->fetch()) {
                                    echo $dias[$h['dia_semana']] . ' ' . substr($h['hora_inicio'],0,5) . '-' . substr($h['hora_fin'],0,5) . '<br>';
                                }
                            ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (($sede_info['fase_actual']??1) == 1): ?>
            <div class="form-section">
                <h4>Asignar profesor a sección</h4>
                <form method="POST" action="crear_seccion.php">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label>Profesor</label>
                            <select name="profesor_id" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($profesores as $p): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo h($p['tipo_cedula'].'-'.$p['numero_documento'].' | '.$p['nombres'].' '.$p['apellidos']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Sección</label>
                            <select name="seccion_id" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($secciones as $sec): ?>
                                <option value="<?php echo $sec['id']; ?>"><?php echo h($sec['materia_nombre'] . ' - Sec ' . $sec['seccion']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" name="asignar_profesor" class="btn-submit">Asignar</button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </section>

        <!-- MÓDULO: SOLICITUD URGENTE DOCENTE -->
        <section id="modulo-solicitudes" class="module-section" style="display:none;">
            <h3>📨 Solicitud Urgente de Registro de Docente</h3>
            <p style="color:#666;margin-bottom:15px;">Use este formulario cuando un profesor no esté registrado en el sistema.</p>

            <div class="form-section">
                <form method="POST">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Tipo de Documento <span class="req">*</span></label>
                            <select name="tipo_documento" required id="tipo-doc-sol" onchange="togglePasaporte()">
                                <option value="V">V - Venezolano</option>
                                <option value="E">E - Extranjero Residente</option>
                                <option value="P">P - Pasaporte</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Número de Documento <span class="req">*</span></label>
                            <input type="text" name="numero_documento" id="num-doc-sol" placeholder="Ej: 12345678 o FR98765432" required>
                        </div>
                        <div class="form-group">
                            <label>Nacionalidad</label>
                            <input type="text" name="nacionalidad" placeholder="Ej: Venezuela, Francia">
                        </div>
                        <div class="form-group">
                            <label>Correo Electrónico <span class="req">*</span></label>
                            <input type="email" name="email" placeholder="docente@ejemplo.com" required>
                        </div>
                        <div class="form-group">
                            <label>Nombres <span class="req">*</span></label>
                            <input type="text" name="nombres" placeholder="Nombres completos" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos <span class="req">*</span></label>
                            <input type="text" name="apellidos" placeholder="Apellidos completos" required>
                        </div>
                    </div>
                    <button type="submit" name="solicitar_docente" class="btn-submit">📨 Enviar Solicitud al Administrador</button>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
function togglePasaporte() {
    const tipo = document.getElementById('tipo-doc-sol').value;
    const input = document.getElementById('num-doc-sol');
    input.placeholder = tipo === 'P' ? 'Ej: FR98765432 (alfanumérico)' : 'Ej: 12345678 (solo números)';
    input.pattern = tipo === 'P' ? '[A-Za-z0-9]+' : '\\d+';
}
</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
