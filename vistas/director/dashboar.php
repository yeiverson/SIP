<?php
// vistas/director/dashboard.php
session_start();

// CANDADO DE SEGURIDAD: Si no hay sesión o el rol no es Director (Rol 7), se le expulsa al login
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 7) {
    header("Location: ../../index.php?error=Acceso+no+autorizado.");
    exit();
}

// Aquí puedes incluir tu archivo de conexión si necesitas hacer consultas en la vista
// require_once '../../config/conexion.php';

$nombre_director = $_SESSION['nombre_full'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Director Global</title>
    
    <link rel="stylesheet" href="../../css/tu_estilo.css">
</head>
<body>

<div class="dashboard-container">
    
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>SIP-Postgrado</h3>
            <p>Rol: Director Global</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active">🏠 Inicio</a>
            <a href="planes.php">📚 Planes de Estudio</a>
            <a href="fases.php">⚙️ Control de Fases</a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="main-content">
        
        <header class="main-header">
            <div class="welcome-text">
                <h2>Bienvenido, <?php echo htmlspecialchars($nombre_director); ?></h2>
                <p>Gestión y Control Estratégico Universitaria</p>
            </div>
            <div class="date-badge">
                <span>Frontera Territorial: Global</span>
            </div>
        </header>

        <section class="cards-grid">
            <div class="card bg-blue">
                <h3>Planes de Estudio</h3>
                <p class="card-desc">Creación de mallas curriculares y asignaturas base.</p>
                <a href="planes.php" class="card-link">Administrar Asignaturas →</a>
            </div>

            <div class="card bg-orange">
                <h3>Control de Fases</h3>
                <p class="card-desc">Activar Planificación (Fase 1) o Inscripciones (Fase 2) por Núcleo.</p>
                <a href="fases.php" class="card-link">Configurar Sedes →</a>
            </div>
        </section>

        <section class="info-section">
            <div class="section-box">
                <h3>Estatus Actual del Ecosistema</h3>
                <p>Desde este panel tiene la facultad de modificar la oferta académica nacional e internacional y regular el flujo operativo de los procesos de admisión e inscripción de postgrado.</p>
            </div>
        </section>

    </main>

</div>

</body>
</html>