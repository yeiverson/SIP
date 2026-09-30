<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';
check_rol(2);

$sede_id = (int)($_SESSION['sede_id'] ?? 0);
$mensaje = '';

$stmt = $pdo->prepare('SELECT id, nombre, fase_actual FROM sedes WHERE id = :id');
$stmt->execute([':id' => $sede_id]);
$sede = $stmt->fetch() ?: [];

if (($sede['fase_actual'] ?? 1) != 1) {
    $mensaje = alerta_error('No puede crear secciones durante la Fase 2 (Inscripciones).');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mensaje === '') {
    $asignatura_codigo = strtoupper(trim((string)($_POST['asignatura_codigo'] ?? '')));
    $seccion = strtoupper(trim((string)($_POST['seccion'] ?? '')));
    $profesor_id = ($_POST['profesor_id'] ?? '') !== '' ? (int)$_POST['profesor_id'] : null;
    $cupo_maximo = max(1, min(50, (int)($_POST['cupo_maximo'] ?? 25)));
    $aula = trim((string)($_POST['aula'] ?? ''));
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    $dia = (int)($_POST['dia_semana'] ?? 0);
    $hora_inicio = trim((string)($_POST['hora_inicio'] ?? ''));
    $hora_fin = trim((string)($_POST['hora_fin'] ?? ''));

    if ($plan_id <= 0 || $sede_id <= 0 || $asignatura_codigo === '' || $seccion === '' || $dia < 1 || $dia > 6 || $hora_inicio === '' || $hora_fin === '' || $hora_inicio >= $hora_fin) {
        $mensaje = alerta_error('Verifique los datos de la sección y el horario.');
    }

    if ($mensaje === '' && $profesor_id !== null) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM horarios h JOIN secciones s ON s.id = h.seccion_id WHERE s.profesor_id = :prof AND h.dia_semana = :dia AND h.hora_inicio < :hora_fin AND h.hora_fin > :hora_inicio AND s.activa = TRUE');
        $stmt->execute([':prof' => $profesor_id, ':dia' => $dia, ':hora_fin' => $hora_fin, ':hora_inicio' => $hora_inicio]);
        if ((int)$stmt->fetchColumn() > 0) {
            $mensaje = alerta_error('El docente ya está ocupado en ese horario.');
        }
    }

    if ($mensaje === '') {
        try {
            $pdo->beginTransaction();
            $periodo = date('Y') . '-' . (date('n') >= 6 ? 'II' : 'I');
            $stmt = $pdo->prepare('INSERT INTO secciones (plan_id, asignatura_codigo, seccion, profesor_id, sede_id, cupo_maximo, aula, periodo) VALUES (:plan, :asig, :seccion, :prof, :sede, :cupo, :aula, :periodo) RETURNING id');
            $stmt->execute([':plan' => $plan_id, ':asig' => $asignatura_codigo, ':seccion' => $seccion, ':prof' => $profesor_id, ':sede' => $sede_id, ':cupo' => $cupo_maximo, ':aula' => $aula !== '' ? $aula : null, ':periodo' => $periodo]);
            $seccion_id = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare('INSERT INTO horarios (seccion_id, dia_semana, hora_inicio, hora_fin) VALUES (:sid, :dia, :inicio, :fin)');
            $stmt->execute([':sid' => $seccion_id, ':dia' => $dia, ':inicio' => $hora_inicio, ':fin' => $hora_fin]);
            $pdo->commit();
            registrar_log($pdo, 'Crear sección', 'secciones', $seccion_id);
            $mensaje = alerta_success("Sección {$seccion} creada exitosamente.");
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[SIP] Error creando sección: ' . $e->getMessage());
            $mensaje = alerta_error('No se pudo crear la sección. Verifique los datos e inténtelo nuevamente.');
        }
    }
}

$planes_stmt = $pdo->prepare('SELECT p.* FROM plan_estudios p JOIN plan_sede ps ON ps.plan_id = p.id WHERE ps.sede_id = :sede AND p.activo = TRUE ORDER BY p.nombre');
$planes_stmt->execute([':sede' => $sede_id]);
$planes = $planes_stmt->fetchAll();
$asignaturas = $pdo->query('SELECT a.* FROM asignaturas a WHERE a.activa = TRUE ORDER BY a.nombre')->fetchAll();
$profesores_stmt = $pdo->prepare('SELECT id, tipo_cedula, numero_documento, nombres, apellidos, email FROM usuarios WHERE rol_id = 3 ORDER BY apellidos, nombres');
$profesores_stmt->execute();
$profesores_lista = $profesores_stmt->fetchAll();
$secciones_stmt = $pdo->prepare("SELECT sec.*, asig.nombre AS materia_nombre, asig.uc, pl.nombre AS plan_nombre, CONCAT_WS(' ', p.tipo_cedula || '-' || p.numero_documento, p.nombres, p.apellidos) AS profesor_nombre FROM secciones sec JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo JOIN plan_estudios pl ON pl.id = sec.plan_id LEFT JOIN usuarios p ON p.id = sec.profesor_id WHERE sec.sede_id = :sede ORDER BY sec.created_at DESC");
$secciones_stmt->execute([':sede' => $sede_id]);
$titulo = 'Crear Secciones';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
<aside class="sidebar"><div class="sidebar-header"><img class="logo-img" src="<?php echo h(asset_url('imagenes/LOGO-1-1.png')); ?>" alt="UNEFA"><div class="sidebar-brand"><h3>SIP-Postgrado</h3><span class="brand-sub">UNEFA</span></div><p>Coord: <?php echo h($_SESSION['nombre_full'] ?? ''); ?></p></div><nav class="sidebar-menu"><a href="dashboard.php">🏠 Inicio</a><a href="crear_seccion.php" class="active">➕ Crear Secciones</a><a href="<?php echo h(asset_url('controlador/cerrar_sesion.php')); ?>" class="logout-btn">🚪 Cerrar Sesión</a></nav></aside>
<main class="main-content"><header class="main-header"><h2>Creación de Secciones y Asignación de Horarios</h2><p>Sede: <strong><?php echo h($sede['nombre'] ?? 'N/A'); ?></strong></p></header>
<?php echo $mensaje; ?>
<div class="form-section"><h3>➕ Nueva Sección</h3><form method="POST"><div class="form-grid-2"><div class="form-group"><label>Plan de Estudios</label><select name="plan_id" required><option value="">Seleccione</option><?php foreach ($planes as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo h($p['nombre']); ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Asignatura</label><select name="asignatura_codigo" required><option value="">Seleccione</option><?php foreach ($asignaturas as $a): ?><option value="<?php echo h($a['codigo']); ?>"><?php echo h($a['codigo'].' - '.$a['nombre']); ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Sección</label><input type="text" name="seccion" maxlength="10" required></div><div class="form-group"><label>Cupo Máximo</label><input type="number" name="cupo_maximo" value="25" min="1" max="50" required></div><div class="form-group"><label>Aula</label><input type="text" name="aula" maxlength="80"></div><div class="form-group"><label>Profesor</label><select name="profesor_id"><option value="">Sin asignar</option><?php foreach ($profesores_lista as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo h($p['tipo_cedula'].'-'.$p['numero_documento'].' | '.$p['nombres'].' '.$p['apellidos']); ?></option><?php endforeach; ?></select></div></div><h4>Horario</h4><div class="form-grid-3"><div class="form-group"><label>Día</label><select name="dia_semana" required><option value="">Seleccione</option><?php foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $i => $dia_nombre): ?><option value="<?php echo $i + 1; ?>"><?php echo $dia_nombre; ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Hora Inicio</label><input type="time" name="hora_inicio" required></div><div class="form-group"><label>Hora Fin</label><input type="time" name="hora_fin" required></div></div><button type="submit" name="crear_seccion" class="btn-submit">Crear Sección</button></form></div>
<div class="table-section"><h3>Secciones Creadas</h3><table class="data-table"><thead><tr><th>#</th><th>Plan</th><th>Asignatura</th><th>Sección</th><th>Profesor</th><th>Cupos</th></tr></thead><tbody><?php while ($sec = $secciones_stmt->fetch()): ?><tr><td><?php echo (int)$sec['id']; ?></td><td><?php echo h($sec['plan_nombre']); ?></td><td><?php echo h($sec['materia_nombre']); ?></td><td><strong><?php echo h($sec['seccion']); ?></strong></td><td><?php echo h($sec['profesor_nombre'] ?? '—'); ?></td><td><?php echo (int)($sec['cupo_actual'] ?? 0); ?>/<?php echo (int)$sec['cupo_maximo']; ?></td></tr><?php endwhile; ?></tbody></table></div></main></div><?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
