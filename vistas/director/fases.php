<?php
// vistas/director/fases.php
session_start();

// 1. CANDADO DE SEGURIDAD: Solo el Director puede gestionar las fases
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 7) {
    header("Location: ../../index.php?error=Acceso+no+autorizado.");
    exit();
}

// 2. CONEXIÓN A TU BASE DE DATOS EXISTENTE
require_once '../../config/conexion.php';

$nombre_director = $_SESSION['nombre_full'];
$mensaje = "";

// 3. PROCESAR EL CAMBIO DE FASE CUANDO EL DIRECTOR PRESIONA EL BOTÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_fase'])) {
    $sede_id    = (int)$_POST['sede_id'];
    $nueva_fase = (int)$_POST['nueva_fase']; // 1 o 2

    if ($sede_id > 0 && ($nueva_fase === 1 || $nueva_fase === 2)) {
        try {
            // Actualiza la fase de la sede elegida en tu tabla de sedes
            $sql_update = "UPDATE sedes SET fase_actual = :fase WHERE id = :sede_id";
            $stmt = $pdo->prepare($sql_update);
            $stmt->execute([
                ':fase'    => $nueva_fase,
                ':sede_id' => $sede_id
            ]);
            $mensaje = "<div class='alert-success'>✅ Configuración de sede actualizada con éxito.</div>";
        } catch (PDOException $e) {
            $mensaje = "<div class='alert-error'>❌ Error al actualizar la fase: " . $e->getMessage() . "</div>";
        }
    }
}

// 4. CONSULTAR LAS SEDES REGISTRADAS PARA MOSTRARLAS EN LA TABLA
// Asumimos que tu tabla 'sedes' tiene al menos: id, nombre, y fase_actual
try {
    $sql_sedes = "SELECT id, nombre, fase_actual FROM sedes ORDER BY nombre ASC";
    $stmt_sedes = $pdo->query($sql_sedes);
    $sedes = $stmt_sedes->fetchAll();
} catch (PDOException $e) {
    $sedes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Fases por Sede | Director</title>
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
            <a href="dashboard.php">🏠 Inicio</a>
            <a href="planes.php">📚 Planes de Estudio</a>
            <a href="fases.php" class="active">⚙️ Control de Fases</a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Control Operativo de Sedes y Núcleos</h2>
            <p>Director: <?php echo htmlspecialchars($nombre_director); ?></p>
        </header>

        <?php echo $mensaje; ?>

        <section class="table-section">
            <h3>Estatus y Apertura de Procesos</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sede / Núcleo</th>
                        <th>Fase Actual</th>
                        <th>Acción de Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sedes) > 0): ?>
                        <?php foreach ($sedes as $sede): ?>
                            <tr>
                                <td><?php echo $sede['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($sede['nombre']); ?></strong></td>
                                <td>
                                    <?php if ($sede['fase_actual'] == 1): ?>
                                        <span class="badge badge-planar">Fase 1: Planificación Académica</span>
                                    <?php else: ?>
                                        <span class="badge badge-inscripcion">Fase 2: Inscripciones Abiertas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="fases.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="sede_id" value="<?php echo $sede['id']; ?>">
                                        
                                        <?php if ($sede['fase_actual'] == 1): ?>
                                            <input type="hidden" name="nueva_fase" value="2">
                                            <button type="submit" name="cambiar_fase" class="btn-action btn-green">🔓 Abrir Inscripciones (Fase 2)</button>
                                        <?php else: ?>
                                            <input type="hidden" name="nueva_fase" value="1">
                                            <button type="submit" name="cambiar_fase" class="btn-action btn-orange">🔒 Cerrar e Ir a Planificación (Fase 1)</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron sedes configuradas en la base de datos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</body>
</html>