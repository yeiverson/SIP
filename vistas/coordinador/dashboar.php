<?php
// vistas/coordinador/dashboard.php
session_start();

// CANDADO DE SEGURIDAD: Solo entra el Coordinador (Rol 2)
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 2) {
    header("Location: ../../index.php?error=Acceso+no+autorizado.");
    exit();
}

require_once '../../config/conexion.php';

$nombre_coord = $_SESSION['nombre_full'];
$sede_id = $_SESSION['sede'];

// Consultar el estatus de la sede para saber si está en Fase 1 (Planificación)
$sql_sede = "SELECT nombre, fase_actual FROM sedes WHERE id = :sede_id";
$stmt = $pdo->prepare($sql_sede);
$stmt->execute([':sede_id' => $sede_id]);
$sede = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Coordinador | Gestión de Oferta</title>
    <link rel="stylesheet" href="../../css/tu_estilo.css">
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <h3>SIP-Postgrado</h3>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active">🏠 Inicio</a>
            <a href="crear_seccion.php">➕ Crear Secciones</a>
            <a href="ver_oferta.php">📋 Oferta Académica</a>
            <a href="../../controlador/cerrar_sesion.php">🚪 Salir</a>
        </nav>
    </aside>

    <main class="main-content">
        <h2>Bienvenido, Coord. <?php echo htmlspecialchars($nombre_coord); ?></h2>
        
        <div class="alert-info">
            Estatus de Sede: <strong><?php echo $sede['nombre']; ?></strong> | 
            Fase Actual: <strong><?php echo ($sede['fase_actual'] == 1) ? "PLANIFICACIÓN (Fase 1)" : "INSCRIPCIÓN (Fase 2)"; ?></strong>
        </div>

        <?php if ($sede['fase_actual'] == 1): ?>
            <section class="cards-grid">
                <div class="card bg-green">
                    <h3>Gestión Académica</h3>
                    <p>Cree secciones, asigne profesores y defina horarios.</p>
                    <a href="crear_seccion.php">Ir a Creación →</a>
                </div>
            </section>
        <?php else: ?>
            <div class="alert-error">
                <p>⚠️ El sistema se encuentra en **Fase de Inscripciones**. Solo puede consultar la oferta.</p>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>