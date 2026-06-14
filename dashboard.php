<?php
session_start();
require_once __DIR__ . '/config/database.php';

$rutas = [
    1 => 'vistas/admin/dashboard.php',
    2 => 'vistas/coordinador/dashboard.php',
    3 => 'vistas/docente/dashboard.php',
    4 => 'vistas/secretaria/dashboard.php',
    5 => 'vistas/aspirante/dashboard.php',
    6 => 'vistas/estudiante/dashboard.php',
    7 => 'vistas/director/dashboard.php',
];

if (isset($_SESSION['rol']) && isset($rutas[$_SESSION['rol']])) {
    header('Location: ' . $rutas[$_SESSION['rol']]);
    exit();
}

if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("SELECT rol_id FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    if ($usuario && isset($rutas[$usuario['rol_id']])) {
        $_SESSION['rol'] = (int)$usuario['rol_id'];
        header('Location: ' . $rutas[$usuario['rol_id']]);
        exit();
    }
}

header('Location: index.php');
exit();

// Legacy content below - kept for backward compatibility with deep links.
$userId = $_SESSION['user_id'] ?? 0;
try {
    $stmt = $pdo->prepare("SELECT id, nombres, apellidos, cedula, tipo_cedula, email FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $usuario = $stmt->fetch();
    
    $nombres_db = trim($usuario['nombres'] ?? '');
    $apellidos_db = trim($usuario['apellidos'] ?? '');

    // Si el usuario es nuevo y el registro le puso "Pendiente"
    if (strtolower($nombres_db) === 'pendiente' && strtolower($apellidos_db) === 'pendiente') {
        $nombreCompleto = "Aspirante (" . $usuario['email'] . ")";
        $primerNombre = "";
        $segundoNombre = "";
        $primerApellido = "";
        $segundoApellido = "";
    } else {
        // Si ya tiene nombre real en la BD, lo armamos bonito
        $nombreCompleto = ucwords(strtolower($nombres_db . ' ' . $apellidos_db));
        
        $partes_nombres = explode(' ', $nombres_db);
        $primerNombre = $partes_nombres[0] ?? '';
        $segundoNombre = $partes_nombres[1] ?? '';

        $partes_apellidos = explode(' ', $apellidos_db);
        $primerApellido = $partes_apellidos[0] ?? '';
        $segundoApellido = $partes_apellidos[1] ?? '';
    }
    
} catch (PDOException $e) {
    die("Error al cargar los datos del usuario: " . $e->getMessage());
}

// Fecha actual en español
$meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
$fechaHoy = $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Registro de Postgrado — UNEFA</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:    #0d1b4b;
      --navy2:   #162260;
      --red:     #b91c1c;
      --gold:    #d4a017;
      --light:   #f2f4f8;
      --white:   #ffffff;
      --text:    #1e293b;
      --muted:   #64748b;
      --border:  #dde2ec;
      --online:  #22c55e;
    }

    body { font-family: 'Inter', sans-serif; background: var(--light); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }
    header { background: var(--navy); position: relative; overflow: hidden; }
    .header-bg { position: absolute; inset: 0; background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/UNEFA_sede_caracas.jpg/1200px-UNEFA_sede_caracas.jpg') center/cover no-repeat; opacity: 0.18; }
    .header-inner { position: relative; display: flex; align-items: center; justify-content: space-between; padding: 14px 32px; gap: 16px; }
    .header-brand { display: flex; align-items: center; gap: 14px; }
    .header-brand img { height: 52px; width: auto; filter: drop-shadow(0 1px 3px rgba(0,0,0,.4)); }
    .header-brand-text { color: var(--white); line-height: 1.25; }
    .header-brand-text strong { display: block; font-size: 1rem; font-weight: 700; letter-spacing: .01em; }
    .header-brand-text span { font-size: .72rem; opacity: .82; font-weight: 400; }
    .btn-logout { background: var(--navy2); border: 1.5px solid rgba(255,255,255,.22); color: var(--white); padding: 9px 22px; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; transition: background .18s, border-color .18s; white-space: nowrap; text-decoration: none; display: inline-block; }
    .btn-logout:hover { background: var(--red); border-color: var(--red); }
    .welcome-wrap { background: var(--white); border-bottom: 1px solid var(--border); padding: 0 32px; }
    .welcome-bar { background: var(--navy); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; margin: 20px 0; flex-wrap: wrap; gap: 12px; }
    .welcome-bar-left h2 { color: var(--white); font-size: 1.15rem; font-weight: 700; letter-spacing: .01em; }
    .welcome-bar-left p { color: rgba(255,255,255,.7); font-size: .8rem; margin-top: 2px; }
    .status-pill { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); border-radius: 999px; padding: 6px 14px; color: var(--white); font-size: .82rem; font-weight: 500; }
    .status-dot { width: 9px; height: 9px; background: var(--online); border-radius: 50%; box-shadow: 0 0 0 3px rgba(34,197,94,.25); animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100% { box-shadow: 0 0 0 3px rgba(34,197,94,.25); } 50% { box-shadow: 0 0 0 6px rgba(34,197,94,.1); } }
    .nav-tabs { display: flex; gap: 4px; padding: 0 32px; background: var(--white); border-bottom: 2px solid var(--border); }
    .nav-tab { padding: 11px 18px; font-size: .82rem; font-weight: 500; color: var(--muted); border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; transition: color .15s, border-color .15s; white-space: nowrap; }
    .nav-tab:hover { color: var(--navy); }
    .nav-tab.active { color: var(--navy); border-color: var(--navy); font-weight: 600; }
    main { flex: 1; padding: 28px 32px; max-width: 1200px; width: 100%; margin: 0 auto; }
    .section-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-bottom: 14px; }
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 28px; }
    .stat-card { background: var(--white); border: 1px solid var(--border); border-radius: 10px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; }
    .stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
    .stat-icon.blue  { background: rgba(13,27,75,.1);  color: var(--navy); }
    .stat-icon.red   { background: rgba(185,28,28,.1); color: var(--red); }
    .stat-icon.gold  { background: rgba(212,160,23,.12); color: var(--gold); }
    .stat-icon.green { background: rgba(34,197,94,.12); color: #16a34a; }
    .stat-info strong { display: block; font-size: 1.35rem; font-weight: 800; color: var(--text); }
    .stat-info span   { font-size: .75rem; color: var(--muted); font-weight: 500; }
    .modules-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .module-card { background: var(--white); border: 1px solid var(--border); border-radius: 12px; padding: 24px 20px; cursor: pointer; transition: transform .18s, box-shadow .18s, border-color .18s; position: relative; overflow: hidden; }
    .module-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--accent, var(--navy)); border-radius: 12px 12px 0 0; }
    .module-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(13,27,75,.12); border-color: rgba(13,27,75,.2); }
    .module-icon { width: 48px; height: 48px; border-radius: 12px; background: var(--icon-bg, rgba(13,27,75,.08)); color: var(--accent, var(--navy)); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 14px; }
    .module-card h3 { font-size: .92rem; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .module-card p { font-size: .76rem; color: var(--muted); line-height: 1.5; }
    .module-arrow { position: absolute; bottom: 16px; right: 16px; color: var(--muted); font-size: .85rem; opacity: 0; transition: opacity .18s, transform .18s; }
    .module-card:hover .module-arrow { opacity: 1; transform: translateX(3px); }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 700px) { .two-col { grid-template-columns: 1fr; } }
    .panel { background: var(--white); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
    .panel-header { padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .panel-header h4 { font-size: .88rem; font-weight: 700; color: var(--text); }
    .panel-header a  { font-size: .76rem; color: var(--navy); text-decoration: none; font-weight: 500; }
    .panel-body { padding: 4px 0; }
    .notice-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 20px; border-bottom: 1px solid var(--border); transition: background .12s; }
    .notice-item:last-child { border-bottom: none; }
    .notice-item:hover { background: var(--light); }
    .notice-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
    .notice-dot.red  { background: var(--red); }
    .notice-dot.gold { background: var(--gold); }
    .notice-dot.blue { background: var(--navy); }
    .notice-text strong { display: block; font-size: .8rem; font-weight: 600; color: var(--text); }
    .notice-text span   { font-size: .73rem; color: var(--muted); }
    .schedule-item { display: flex; align-items: center; gap: 14px; padding: 11px 20px; border-bottom: 1px solid var(--border); }
    .schedule-item:last-child { border-bottom: none; }
    .schedule-day { min-width: 40px; text-align: center; background: rgba(13,27,75,.06); border-radius: 8px; padding: 6px 4px; font-size: .7rem; font-weight: 700; color: var(--navy); text-transform: uppercase; line-height: 1.2; }
    .schedule-info strong { display: block; font-size: .8rem; font-weight: 600; }
    .schedule-info span   { font-size: .73rem; color: var(--muted); }
    .input-icon-row { display: flex; align-items: center; border: 1.5px solid var(--border); border-radius: 7px; overflow: hidden; transition: border-color .15s; }
    .input-icon-row:focus-within { border-color: var(--navy); }
    .input-icon { padding: 0 10px; font-size: .85rem; color: var(--muted); background: var(--light); border-right: 1px solid var(--border); height: 38px; display: flex; align-items: center; font-weight: 700; }
    .input-icon-row input { border: none !important; border-radius: 0 !important; flex: 1; }
    .input-icon-row input:focus { outline: none; }
    .baremo-table-wrap { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin-bottom: 4px; }
    .baremo-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    .baremo-table thead tr { background: var(--navy); color: var(--white); }
    .baremo-table thead th { padding: 11px 16px; text-align: left; font-weight: 600; font-size: .78rem; }
    .baremo-table thead th:nth-child(2), .baremo-table thead th:nth-child(3) { text-align: center; width: 60px; }
    .baremo-table tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    .baremo-table tbody tr:last-child { border-bottom: none; }
    .baremo-table tbody tr:hover:not(.baremo-category) { background: var(--light); }
    .baremo-table td { padding: 10px 16px; color: var(--text); }
    .baremo-table td:nth-child(2), .baremo-table td:nth-child(3) { text-align: center; }
    .baremo-table input[type=radio] { width: 16px; height: 16px; accent-color: var(--navy); cursor: pointer; }
    .baremo-category td { background: rgba(13,27,75,.06); color: var(--navy); font-weight: 700; font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; padding: 8px 16px; }
    .docs-instructions { background: rgba(212,160,23,.08); border: 1px solid rgba(212,160,23,.25); border-radius: 8px; padding: 12px 16px; font-size: .78rem; color: var(--text); }
    .docs-instructions strong { color: var(--navy); display: block; margin-bottom: 6px; }
    .docs-instructions ul { padding-left: 18px; }
    .docs-instructions li { margin-bottom: 3px; }
    .file-upload-box input[type=file] { display: none; }
    .file-upload-label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .file-btn { background: var(--navy); color: var(--white); padding: 7px 14px; border-radius: 6px; font-size: .78rem; font-weight: 600; white-space: nowrap; transition: background .15s; }
    .file-btn:hover { background: var(--navy2); }
    .file-name { font-size: .78rem; color: var(--muted); }
    .file-upload-box small { display: block; margin-top: 4px; font-size: .7rem; color: var(--muted); }
    footer { background: #1a1a1a; color: rgba(255,255,255,.65); text-align: center; padding: 18px 32px; font-size: .75rem; line-height: 1.8; border-top: 3px solid var(--navy); }
    footer strong { color: rgba(255,255,255,.9); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .tab-preinscripcion { color: var(--red) !important; font-weight: 600 !important; border-color: transparent; }
    .tab-preinscripcion.active { border-color: var(--red) !important; color: var(--red) !important; }
    .preinsc-intro { display: flex; align-items: flex-start; gap: 18px; background: var(--navy); color: var(--white); border-radius: 12px; padding: 22px 26px; margin-bottom: 28px; }
    .preinsc-intro-icon { font-size: 2.2rem; flex-shrink: 0; }
    .preinsc-intro h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 6px; }
    .preinsc-intro p  { font-size: .82rem; opacity: .85; line-height: 1.6; }
    .preinsc-intro strong { color: var(--gold); }
    .stepper { display: flex; align-items: center; margin-bottom: 28px; overflow-x: auto; padding-bottom: 4px; }
    .step { display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 90px; cursor: default; }
    .step-circle { width: 34px; height: 34px; border-radius: 50%; background: var(--border); color: var(--muted); display: flex; align-items: center; justify-content: center; font-size: .85rem; font-weight: 700; transition: background .2s, color .2s; }
    .step.active .step-circle { background: var(--navy); color: var(--white); }
    .step.done .step-circle   { background: #16a34a; color: var(--white); }
    .step span { font-size: .7rem; color: var(--muted); font-weight: 500; text-align: center; }
    .step.active span { color: var(--navy); font-weight: 700; }
    .step-line { flex: 1; height: 2px; background: var(--border); min-width: 30px; margin-top: -14px; }
    .form-card { background: var(--white); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
    .form-card-header { background: var(--light); border-bottom: 1px solid var(--border); padding: 18px 24px; }
    .form-step-badge { display: inline-block; background: var(--navy); color: var(--white); font-size: .68rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; margin-bottom: 8px; letter-spacing: .04em; }
    .form-card-header h3 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
    .form-card-header p  { font-size: .78rem; color: var(--muted); }
    .form-body { padding: 24px; }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 640px) { .form-grid-2 { grid-template-columns: 1fr; } }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group.full { grid-column: 1 / -1; }
    .form-group label { font-size: .78rem; font-weight: 600; color: var(--text); }
    .req { color: var(--red); }
    .form-group input, .form-group select, .form-group textarea { border: 1.5px solid var(--border); border-radius: 7px; padding: 9px 12px; font-size: .82rem; font-family: inherit; color: var(--text); background: var(--white); transition: border-color .15s; resize: vertical; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--navy); }
    .cedula-row { display: flex; gap: 8px; }
    .cedula-row select { width: 70px; flex-shrink: 0; }
    .cedula-row input  { flex: 1; }
    .sub-section { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--navy); margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 2px solid var(--navy); }
    .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); }
    .btn-siguiente, .btn-anterior, .btn-enviar { padding: 10px 26px; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; border: none; transition: background .15s, transform .1s; }
    .btn-siguiente { background: var(--navy); color: var(--white); }
    .btn-siguiente:hover { background: var(--navy2); }
    .btn-anterior  { background: var(--light); color: var(--text); border: 1.5px solid var(--border); }
    .btn-anterior:hover { background: var(--border); }
    .btn-enviar    { background: #16a34a; color: var(--white); padding: 10px 32px; }
    .btn-enviar:hover { background: #15803d; }
  </style>
</head>
<body>

<header>
  <div class="header-bg"></div>
  <div class="header-inner">
    <div class="header-brand">
      <img src="imagenes/escudo-png.png" alt="Logo UNEFA" />
      <div class="header-brand-text">
        <strong>Universidad Nacional Experimental Politécnica</strong>
        <span>de la Fuerza Armada Nacional Bolivariana (UNEFA)</span>
      </div>
    </div>
    <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
  </div>
</header>

<div class="welcome-wrap">
  <div class="welcome-bar">
    <div class="welcome-bar-left">
      <h2>Bienvenido, <?php echo htmlspecialchars($nombreCompleto); ?></h2>
      <p>Sistema de Registro de Postgrado &nbsp;·&nbsp; <?php echo $fechaHoy; ?></p>
    </div>
    <div class="status-pill">
      <span class="status-dot"></span>
      En Línea
    </div>
  </div>
</div>

<nav class="nav-tabs">
  <div class="nav-tab active" data-tab="inicio">Inicio</div>
  <div class="nav-tab" data-tab="expediente">Mi Expediente</div>
  <div class="nav-tab" data-tab="inscripciones">Inscripciones</div>
  <div class="nav-tab" data-tab="pagos">Pagos</div>
  <div class="nav-tab" data-tab="documentos">Documentos</div>
  <div class="nav-tab tab-preinscripcion" data-tab="preinscripcion">Pre-Inscripción / Baremo</div>
</nav>

<main>
<div id="tab-inicio" class="tab-content active">

  <div class="section-title">Resumen Académico</div>
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon blue">📚</div>
      <div class="stat-info">
        <strong>6</strong>
        <span>Materias Inscritas</span>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon gold">🎓</div>
      <div class="stat-info">
        <strong>18 UC</strong>
        <span>Unidades Crédito</span>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">✔</div>
      <div class="stat-info">
        <strong>Al día</strong>
        <span>Estado de Solvencia</span>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red">📋</div>
      <div class="stat-info">
        <strong>2</strong>
        <span>Solicitudes Pendientes</span>
      </div>
    </div>
  </div>

  <div class="section-title">Módulos del Sistema</div>
  <div class="modules-grid">
    <div class="module-card" style="--accent:#0d1b4b; --icon-bg:rgba(13,27,75,.08)">
      <div class="module-icon">📝</div>
      <h3>Inscripción de Materias</h3>
      <p>Registra y gestiona tu carga académica del período vigente.</p>
      <span class="module-arrow">→</span>
    </div>
    <div class="module-card" style="--accent:#b91c1c; --icon-bg:rgba(185,28,28,.08)">
      <div class="module-icon">💳</div>
      <h3>Pagos y Aranceles</h3>
      <p>Consulta y registra tus pagos, solvencias y recibos.</p>
      <span class="module-arrow">→</span>
    </div>
    <div class="module-card" style="--accent:#d4a017; --icon-bg:rgba(212,160,23,.1)">
      <div class="module-icon">📄</div>
      <h3>Expediente Académico</h3>
      <p>Revisa tus notas, historial y estado de avance del programa.</p>
      <span class="module-arrow">→</span>
    </div>
    <div class="module-card" style="--accent:#0d1b4b; --icon-bg:rgba(13,27,75,.08)">
      <div class="module-icon">🗂</div>
      <h3>Documentos</h3>
      <p>Descarga constancias, certificados y recaudos requeridos.</p>
      <span class="module-arrow">→</span>
    </div>
    <div class="module-card" style="--accent:#16a34a; --icon-bg:rgba(34,197,94,.08)">
      <div class="module-icon">📅</div>
      <h3>Horarios</h3>
      <p>Consulta el horario de clases y calendario académico.</p>
      <span class="module-arrow">→</span>
    </div>
  </div>

  <div class="two-col">
    <div>
      <div class="section-title">Avisos y Notificaciones</div>
      <div class="panel">
        <div class="panel-header">
          <h4>Recientes</h4>
          <a href="#">Ver todos →</a>
        </div>
        <div class="panel-body">
          <div class="notice-item">
            <span class="notice-dot red"></span>
            <div class="notice-text">
              <strong>Período de inscripción abierto</strong>
              <span>Vence el 30 de Abril de 2026</span>
            </div>
          </div>
          <div class="notice-item">
            <span class="notice-dot gold"></span>
            <div class="notice-text">
              <strong>Pago de arancel pendiente</strong>
              <span>Segundo trimestre 2026</span>
            </div>
          </div>
          <div class="notice-item">
            <span class="notice-dot blue"></span>
            <div class="notice-text">
              <strong>Actualización de datos personales</strong>
              <span>Requerida por Secretaría</span>
            </div>
          </div>
          <div class="notice-item">
            <span class="notice-dot blue"></span>
            <div class="notice-text">
              <strong>Calendario académico publicado</strong>
              <span>Trimestre Abril–Julio 2026</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="section-title">Próximas Clases</div>
      <div class="panel">
        <div class="panel-header">
          <h4>Esta semana</h4>
          <a href="#">Ver horario →</a>
        </div>
        <div class="panel-body">
          <div class="schedule-item">
            <div class="schedule-day">Lun<br/>21</div>
            <div class="schedule-info">
              <strong>Metodología de la Investigación</strong>
              <span>8:00 am – 12:00 m &nbsp;·&nbsp; Aula 304</span>
            </div>
          </div>
          <div class="schedule-item">
            <div class="schedule-day">Mar<br/>22</div>
            <div class="schedule-info">
              <strong>Gerencia Estratégica</strong>
              <span>2:00 pm – 6:00 pm &nbsp;·&nbsp; Aula 201</span>
            </div>
          </div>
          <div class="schedule-item">
            <div class="schedule-day">Jue<br/>24</div>
            <div class="schedule-info">
              <strong>Estadística Aplicada</strong>
              <span>8:00 am – 12:00 m &nbsp;·&nbsp; Lab. Cómputo</span>
            </div>
          </div>
          <div class="schedule-item">
            <div class="schedule-day">Vie<br/>25</div>
            <div class="schedule-info">
              <strong>Derecho Administrativo</strong>
              <span>2:00 pm – 6:00 pm &nbsp;·&nbsp; Aula 104</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><div id="tab-preinscripcion" class="tab-content" style="display:none">

  <form id="form-preinscripcion" action="procesar_baremo.php" method="POST" enctype="multipart/form-data">

    <div class="preinsc-intro">
      <div class="preinsc-intro-icon">📋</div>
      <div>
        <h2>Preinscripción SIP-Postgrado</h2>
        <p>Complete los campos a continuación. Este formulario está dirigido a aspirantes que <strong>no son estudiantes regulares</strong> de la UNEFA.</p>
      </div>
    </div>

    <div class="stepper">
      <div class="step active" data-step="1"><div class="step-circle">1</div><span>Datos Personales</span></div>
      <div class="step-line"></div>
      <div class="step" data-step="2"><div class="step-circle">2</div><span>Dirección</span></div>
      <div class="step-line"></div>
      <div class="step" data-step="3"><div class="step-circle">3</div><span>Contacto</span></div>
      <div class="step-line"></div>
      <div class="step" data-step="4"><div class="step-circle">4</div><span>Académico / Laboral</span></div>
      <div class="step-line"></div>
      <div class="step" data-step="5"><div class="step-circle">5</div><span>Entrevista / Docs</span></div>
    </div>

    <div class="form-card" id="paso-1">
      <div class="form-card-header">
        <span class="form-step-badge">Paso 1 de 5</span>
        <h3>Datos Personales</h3>
      </div>
      <div class="form-body">
        <div class="form-grid-2">
          <div class="form-group">
            <label>Tipo de documento <span class="req">*</span></label>
            <select name="tipo_documento">
              <option value="V" <?php echo ($usuario['tipo_cedula'] == 'V') ? 'selected' : ''; ?>>V — Venezolano</option>
              <option value="E" <?php echo ($usuario['tipo_cedula'] == 'E') ? 'selected' : ''; ?>>E — Extranjero</option>
            </select>
          </div>
          <div class="form-group">
            <label>Cédula de Identidad <span class="req">*</span></label>
            <input type="text" name="cedula" value="<?php echo htmlspecialchars($usuario['cedula'] ?? ''); ?>" readonly />
          </div>
          <div class="form-group">
            <label>Primer nombre <span class="req">*</span></label>
            <input type="text" name="primer_nombre" value="<?php echo htmlspecialchars($primerNombre); ?>" placeholder="Escribe tu primer nombre" />
          </div>
          <div class="form-group">
            <label>Segundo nombre</label>
            <input type="text" name="segundo_nombre" value="<?php echo htmlspecialchars($segundoNombre); ?>" placeholder="Escribe tu segundo nombre" />
          </div>
          <div class="form-group">
            <label>Primer apellido <span class="req">*</span></label>
            <input type="text" name="primer_apellido" value="<?php echo htmlspecialchars($primerApellido); ?>" placeholder="Escribe tu primer apellido" />
          </div>
          <div class="form-group">
            <label>Segundo apellido <span class="req">*</span></label>
            <input type="text" name="segundo_apellido" value="<?php echo htmlspecialchars($segundoApellido); ?>" placeholder="Escribe tu segundo apellido" />
          </div>
          <div class="form-group">
            <label>Fecha de nacimiento <span class="req">*</span></label>
            <input type="date" name="fecha_nacimiento" />
          </div>
          <div class="form-group">
            <label>Sexo <span class="req">*</span></label>
            <select name="sexo">
              <option value="">Seleccione</option>
              <option value="Femenino">Femenino</option>
              <option value="Masculino">Masculino</option>
            </select>
          </div>
          <div class="form-group full">
            <label>Estado civil <span class="req">*</span></label>
            <select name="estado_civil">
              <option value="">Seleccione</option>
              <option value="Soltero/a">Soltero/a</option>
              <option value="Casado/a">Casado/a</option>
              <option value="Divorciado/a">Divorciado/a</option>
              <option value="Viudo/a">Viudo/a</option>
              <option value="Union estable">Unión estable de hecho</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <span></span>
          <button type="button" class="btn-siguiente" onclick="irPaso(2)">Siguiente →</button>
        </div>
      </div>
    </div>

    <div class="form-card" id="paso-2" style="display:none">
      <div class="form-card-header">
        <span class="form-step-badge">Paso 2 de 5</span>
        <h3>Dirección de habitación</h3>
      </div>
      <div class="form-body">
        <div class="form-grid-2">
          <div class="form-group">
            <label>Estado <span class="req">*</span></label>
            <select name="estado">
              <option value="">Seleccione</option>
              <option>Amazonas</option><option>Anzoátegui</option><option>Apure</option>
              <option>Aragua</option><option>Barinas</option><option>Bolívar</option>
              <option>Carabobo</option><option>Cojedes</option><option>Delta Amacuro</option>
              <option>Distrito Capital</option><option>Falcón</option><option>Guárico</option>
              <option>Lara</option><option>Mérida</option><option>Miranda</option>
              <option>Monagas</option><option>Nueva Esparta</option><option>Portuguesa</option>
              <option>Sucre</option><option>Táchira</option><option>Trujillo</option>
              <option>Vargas</option><option>Yaracuy</option><option>Zulia</option>
            </select>
          </div>
          <div class="form-group">
            <label>Municipio <span class="req">*</span></label>
            <select name="municipio">
              <option value="">Seleccione</option>
              <option>Libertador</option><option>Sucre</option><option>Baruta</option>
              <option>Chacao</option><option>El Hatillo</option><option>Otro</option>
            </select>
          </div>
          <div class="form-group full">
            <label>Ciudad / Pueblo <span class="req">*</span></label>
            <input type="text" name="ciudad" placeholder="Escribe tu ciudad o pueblo" />
          </div>
          <div class="form-group">
            <label>Avenida / Calle / Vereda <span class="req">*</span></label>
            <input type="text" name="avenida" placeholder="Escribe tu avenida o calle" />
          </div>
          <div class="form-group">
            <label>Urbanización / Barrio / Sector</label>
            <input type="text" name="urbanizacion" placeholder="Escribe tu urbanización o barrio" />
          </div>
          <div class="form-group">
            <label>Tipo de residencia <span class="req">*</span></label>
            <select name="tipo_residencia">
              <option value="">Seleccione</option>
              <option value="Casa">Casa</option>
              <option value="Apartamento">Apartamento</option>
              <option value="Quinta">Quinta</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Residencia (nombre o N° casa / edificio)</label>
            <input type="text" name="residencia" placeholder="Casa o edificio" />
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-anterior" onclick="irPaso(1)">← Atrás</button>
          <button type="button" class="btn-siguiente" onclick="irPaso(3)">Siguiente →</button>
        </div>
      </div>
    </div>

    <div class="form-card" id="paso-3" style="display:none">
      <div class="form-card-header">
        <span class="form-step-badge">Paso 3 de 5</span>
        <h3>Redes sociales y Contacto</h3>
      </div>
      <div class="form-body">
        <h4 class="sub-section">Redes Sociales</h4>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Twitter (X)</label>
            <div class="input-icon-row"><span class="input-icon">𝕏</span><input type="text" name="twitter" placeholder="Tu usuario" /></div>
          </div>
          <div class="form-group">
            <label>Facebook</label>
            <div class="input-icon-row"><span class="input-icon">f</span><input type="text" name="facebook" placeholder="Tu usuario" /></div>
          </div>
          <div class="form-group">
            <label>Instagram</label>
            <div class="input-icon-row"><span class="input-icon">◎</span><input type="text" name="instagram" placeholder="Tu usuario" /></div>
          </div>
          <div class="form-group">
            <label>LinkedIn</label>
            <div class="input-icon-row"><span class="input-icon">in</span><input type="text" name="linkedin" placeholder="Tu usuario" /></div>
          </div>
        </div>

        <h4 class="sub-section">Contacto y condición</h4>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Teléfono fijo <span class="req">*</span></label>
            <div class="input-icon-row"><span class="input-icon">☎</span><input type="tel" name="telefono" placeholder="11 dígitos, inicia con 0" /></div>
          </div>
          <div class="form-group">
            <label>Celular <span class="req">*</span></label>
            <div class="input-icon-row"><span class="input-icon">📱</span><input type="tel" name="celular" placeholder="11 dígitos, inicia con 0" /></div>
          </div>
          <div class="form-group">
            <label>Condición de ingreso <span class="req">*</span></label>
            <select name="condicion_ingreso">
              <option value="">Seleccione</option>
              <option value="Nuevo ingreso">Nuevo ingreso</option>
              <option value="Reingreso">Reingreso</option>
              <option value="Equivalencia">Equivalencia</option>
              <option value="Traslado">Traslado</option>
            </select>
          </div>
          <div class="form-group">
            <label>Condición del usuario <span class="req">*</span></label>
            <select name="condicion_usuario">
              <option value="">Seleccione</option>
              <option value="Civil">Civil</option>
              <option value="Militar activo">Militar activo</option>
              <option value="Militar retirado">Militar retirado</option>
              <option value="Funcionario publico">Funcionario público</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-anterior" onclick="irPaso(2)">← Atrás</button>
          <button type="button" class="btn-siguiente" onclick="irPaso(4)">Siguiente →</button>
        </div>
      </div>
    </div>

    <div class="form-card" id="paso-4" style="display:none">
      <div class="form-card-header">
        <span class="form-step-badge">Paso 4 de 5</span>
        <h3>Datos Académicos y Laborales</h3>
      </div>
      <div class="form-body">
        <h4 class="sub-section">Datos Académicos</h4>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Área de conocimiento <span class="req">*</span></label>
            <input type="text" name="area_conocimiento" placeholder="Área" />
          </div>
          <div class="form-group">
            <label>Nivel académico <span class="req">*</span></label>
            <input type="text" name="nivel_academico" placeholder="Ej: Pregrado, Especialización" />
          </div>
          <div class="form-group">
            <label>Universidad <span class="req">*</span></label>
            <input type="text" name="universidad" placeholder="Universidad" />
          </div>
          <div class="form-group">
            <label>Título obtenido <span class="req">*</span></label>
            <input type="text" name="titulo_obtenido" placeholder="Título" />
          </div>
          <div class="form-group">
            <label>Año de graduación <span class="req">*</span></label>
            <select name="ano_graduacion">
              <option value="">Seleccione</option>
              <option value="2026">2026</option><option value="2025">2025</option><option value="2024">2024</option>
              <option value="2023">2023</option><option value="2022">2022</option><option value="2021">2021</option>
              <option value="2020">2020</option><option value="2019">2019</option><option value="2018">2018</option>
              <option value="2017">2017</option><option value="2016">2016</option><option value="2015">2015</option>
              <option value="2014">2014</option><option value="2013">2013</option><option value="2012">2012</option>
              <option value="2010">2010</option><option value="Antes de 2010">Antes de 2010</option>
            </select>
          </div>
          <div class="form-group">
            <label>Promedio de calificaciones <span class="req">*</span></label>
            <input type="number" name="promedio" placeholder="Ej: 16 o 16.5" min="0" max="20" step="0.1" />
          </div>
        </div>

        <h4 class="sub-section">Datos Laborales</h4>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Tipo de institución <span class="req">*</span></label>
            <select name="tipo_institucion">
              <option value="">Seleccione</option>
              <option value="Publica">Pública</option>
              <option value="Privada">Privada</option>
              <option value="Mixta">Mixta</option>
              <option value="ONG">ONG</option>
              <option value="Fuerzas Armadas">Fuerzas Armadas</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Nombre de la institución u organismo <span class="req">*</span></label>
            <input type="text" name="nombre_institucion" placeholder="Nombre" />
          </div>
          <div class="form-group">
            <label>Antigüedad <span class="req">*</span></label>
            <input type="text" name="antiguedad" placeholder="Años / meses" />
          </div>
          <div class="form-group">
            <label>Teléfono (trabajo)</label>
            <input type="tel" name="telefono_trabajo" placeholder="Opcional, 11 dígitos si indica" />
          </div>
          <div class="form-group">
            <label>Cargo <span class="req">*</span></label>
            <input type="text" name="cargo" placeholder="Cargo" />
          </div>
          <div class="form-group">
            <label>¿Trabaja en la UNEFA? <span class="req">*</span></label>
            <select name="trabaja_unefa">
              <option value="">Seleccione</option>
              <option value="si">Sí</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-anterior" onclick="irPaso(3)">← Atrás</button>
          <button type="button" class="btn-siguiente" onclick="irPaso(5)">Siguiente →</button>
        </div>
      </div>
    </div>

    <div class="form-card" id="paso-5" style="display:none">
      <div class="form-card-header">
        <span class="form-step-badge">Paso 5 de 5</span>
        <h3>Aspectos para la Entrevista</h3>
        <p>Responda Sí o No según corresponda a su perfil</p>
      </div>
      <div class="form-body">

        <div class="baremo-table-wrap">
          <table class="baremo-table">
            <thead>
              <tr>
                <th>Aspectos a Evaluar</th>
                <th>Sí</th>
                <th>No</th>
              </tr>
            </thead>
            <tbody>
              <tr class="baremo-category"><td colspan="3">Académico</td></tr>
              <tr>
                <td>Participación en eventos científicos nacionales e internacionales.</td>
                <td><input type="radio" name="baremo[1]" value="si" /></td>
                <td><input type="radio" name="baremo[1]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Participación como jurado o tutor en trabajos de investigación.</td>
                <td><input type="radio" name="baremo[2]" value="si" /></td>
                <td><input type="radio" name="baremo[2]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Disposición a participar en actividades académicas, investigación e institucionales.</td>
                <td><input type="radio" name="baremo[3]" value="si" /></td>
                <td><input type="radio" name="baremo[3]" value="no" checked /></td>
              </tr>

              <tr class="baremo-category"><td colspan="3">Investigación</td></tr>
              <tr>
                <td>Tema de interés específico para su investigación vinculada a la nación.</td>
                <td><input type="radio" name="baremo[4]" value="si" /></td>
                <td><input type="radio" name="baremo[4]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Vinculación entre el área profesional con los estudios de postgrado.</td>
                <td><input type="radio" name="baremo[5]" value="si" /></td>
                <td><input type="radio" name="baremo[5]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Afiliación a grupo o red de investigadores.</td>
                <td><input type="radio" name="baremo[6]" value="si" /></td>
                <td><input type="radio" name="baremo[6]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Participación como evaluador en artículos científicos.</td>
                <td><input type="radio" name="baremo[7]" value="si" /></td>
                <td><input type="radio" name="baremo[7]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Ha escrito o publicado artículos científicos (Opción A).</td>
                <td><input type="radio" name="baremo[8]" value="si" /></td>
                <td><input type="radio" name="baremo[8]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Ha escrito o publicado artículos científicos (Opción B).</td>
                <td><input type="radio" name="baremo[9]" value="si" /></td>
                <td><input type="radio" name="baremo[9]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Familiarización con las líneas de investigación de la Universidad.</td>
                <td><input type="radio" name="baremo[10]" value="si" /></td>
                <td><input type="radio" name="baremo[10]" value="no" checked /></td>
              </tr>
              <tr>
                <td>La investigación satisface fines personales o institucionales.</td>
                <td><input type="radio" name="baremo[11]" value="si" /></td>
                <td><input type="radio" name="baremo[11]" value="no" checked /></td>
              </tr>

              <tr class="baremo-category"><td colspan="3">Otros</td></tr>
              <tr>
                <td>Acceso y disponibilidad al manejo de equipos tecnológicos.</td>
                <td><input type="radio" name="baremo[12]" value="si" /></td>
                <td><input type="radio" name="baremo[12]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Disponibilidad personal para financiar los estudios (Opción A).</td>
                <td><input type="radio" name="baremo[13]" value="si" /></td>
                <td><input type="radio" name="baremo[13]" value="no" checked /></td>
              </tr>
              <tr>
                <td>Disponibilidad personal para financiar los estudios (Opción B).</td>
                <td><input type="radio" name="baremo[14]" value="si" /></td>
                <td><input type="radio" name="baremo[14]" value="no" checked /></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="form-group" style="margin-top:20px">
          <label>Debe explicar: Tema de interés específico para su investigación vinculados a las áreas prioritarias de desarrollo de la nación: <span class="req">*</span></label>
          <textarea rows="4" name="tema_interes" placeholder="Describa su tema de interés investigativo..."></textarea>
        </div>

        <h4 class="sub-section">Otros datos</h4>
        <div class="form-grid-2">
          <div class="form-group">
            <label>¿Cuenta con beca?</label>
            <select name="beca">
              <option value="">Seleccione</option>
              <option value="No">No</option>
              <option value="Beca MPPEU">Beca MPPEU</option>
              <option value="Beca UNEFA">Beca UNEFA</option>
              <option value="Beca Fundayacucho">Beca Fundayacucho</option>
              <option value="Otra beca">Otra beca</option>
            </select>
          </div>
          <div class="form-group">
            <label>Fecha de ingreso a la UNEFA</label>
            <input type="date" name="fecha_ingreso_unefa" />
          </div>
        </div>

        <h4 class="sub-section">Adjuntar Documentos</h4>
        <div class="docs-instructions">
          <strong>Instrucciones:</strong>
          <ul>
            <li>Solo se aceptan archivos de imagen en formato JPG o PNG.</li>
            <li>Las imágenes deben ser en Color, Claras y Legibles.</li>
            <li>La resolución recomendada es de 1400 x 1400.</li>
          </ul>
        </div>
        <div class="form-grid-2" style="margin-top:14px">
          <div class="form-group">
            <label>Documento de Identidad <span class="req">*</span></label>
            <div class="file-upload-box">
              <input type="file" name="archivo_ci" accept=".jpg,.png" id="file-ci" onchange="updateFileName(this,'lbl-ci')" />
              <label for="file-ci" class="file-upload-label">
                <span class="file-btn">Seleccionar archivo</span>
                <span class="file-name" id="lbl-ci">Sin archivos seleccionados</span>
              </label>
              <small>Solo se aceptan formatos JPG y PNG</small>
            </div>
          </div>
          <div class="form-group">
            <label>Título <span class="req">*</span></label>
            <div class="file-upload-box">
              <input type="file" name="archivo_titulo" accept=".jpg,.png" id="file-titulo" onchange="updateFileName(this,'lbl-titulo')" />
              <label for="file-titulo" class="file-upload-label">
                <span class="file-btn">Seleccionar archivo</span>
                <span class="file-name" id="lbl-titulo">Sin archivos seleccionados</span>
              </label>
              <small>Solo se aceptan formatos JPG y PNG</small>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-anterior" onclick="irPaso(4)">← Atrás</button>
          <button type="button" class="btn-enviar" onclick="enviarPreinscripcion()">Finalizar Registro</button>
        </div>
      </div>
    </div>

  </form>
</div></main>

<footer>
  <strong>UNEFA</strong> | Excelencia Educativa Abierta al Pueblo<br/>
  Vicerrectorado de Investigación, Postgrado y Recreación
</footer>

<script>
  // Pestañas de navegación
  document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const target = tab.dataset.tab;
      document.querySelectorAll('.tab-content').forEach(c => {
        c.style.display = 'none';
        c.classList.remove('active');
      });
      const section = document.getElementById('tab-' + target);
      if (section) {
        section.style.display = 'block';
        section.classList.add('active');
      } else {
        document.getElementById('tab-inicio').style.display = 'block';
        document.getElementById('tab-inicio').classList.add('active');
      }
    });
  });

  // Multi-step form del Baremo
  let pasoActual = 1;

  function irPaso(n) {
    document.getElementById('paso-' + pasoActual).style.display = 'none';
    
    document.querySelectorAll('.step').forEach(s => {
      const sn = parseInt(s.dataset.step);
      s.classList.remove('active','done');
      if (sn < n) s.classList.add('done');
      if (sn === n) s.classList.add('active');
      if (sn < n) s.querySelector('.step-circle').textContent = '✓';
      else s.querySelector('.step-circle').textContent = sn;
    });
    
    pasoActual = n;
    document.getElementById('paso-' + n).style.display = 'block';
  }

  function updateFileName(input, labelId) {
    const label = document.getElementById(labelId);
    if (input.files && input.files[0]) {
      label.textContent = input.files[0].name;
      label.style.color = 'var(--navy)';
    } else {
      label.textContent = 'Sin archivos seleccionados';
      label.style.color = '';
    }
  }

  function enviarPreinscripcion() {
    if(confirm('¿Estás seguro de enviar la pre-inscripción y el baremo?')) {
        document.getElementById('form-preinscripcion').submit();
    }
  }
</script>

</body>
</html>