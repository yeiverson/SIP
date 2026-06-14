<?php
// vistas/director/planes.php
session_start();

// 1. CANDADO DE SEGURIDAD: Solo entra el Director
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 7) {
    header("Location: ../../index.php?error=Acceso+no+autorizado.");
    exit();
}

// 2. IMPORTAR CONEXIÓN A POSTGRESQL
// Subimos dos niveles (../../) para salir de vistas/director/ y entrar a config/
require_once '../../config/conexion.php';

$nombre_director = $_SESSION['nombre_full'];
$mensaje = "";

// 3. PROCESAR FORMULARIO CUANDO SE ENVÍA (INSERTAR MATERIA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_materia'])) {
    $codigo = strtoupper(trim($_POST['codigo_materia']));
    $nombre = trim($_POST['nombre_materia']);
    $uc     = (int)$_POST['uc_materia'];

    if (!empty($codigo) && !empty($nombre) && $uc > 0) {
        try {
            // Consulta preparada para evitar duplicados e inyección SQL
            $sql_insert = "INSERT INTO asignaturas (codigo, nombre, uc) VALUES (:codigo, :nombre, :uc)";
            $stmt = $pdo->prepare($sql_insert);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':uc'     => $uc
            ]);
            $mensaje = "<div class='alert-success'>✅ Asignatura registrada con éxito.</div>";
        } catch (PDOException $e) {
            // Error clave 23505 en Postgres significa que el código ya existe (Llave primaria duplicada)
            if ($e->getCode() == '23505') {
                $mensaje = "<div class='alert-error'>❌ Error: El código de materia ya está registrado.</div>";
            } else {
                $mensaje = "<div class='alert-error'>❌ Error en la base de datos: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        $mensaje = "<div class='alert-error'>❌ Por favor, llene todos los campos correctamente.</div>";
    }
}

// 4. CONSULTAR LAS MATERIAS EXISTENTES PARA MOSTRARLAS EN LA TABLA
try {
    $sql_select = "SELECT codigo, nombre, uc FROM asignaturas ORDER BY nombre ASC";
    $stmt_materias = $pdo->query($sql_select);
    $materias = $stmt_materias->fetchAll();
} catch (PDOException $e) {
    $materias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Planes de Estudio | Director</title>
    <link rel="stylesheet" href="../../css/tu_estilo.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="../../imagenes/sip.ico">
</head>
<body>

<div class="dashboard-container">
    
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">UNEFA</span>
            </div>
            <p>Rol: Director Global</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php">🏠 Inicio</a>
            <a href="planes.php" class="active">📚 Planes de Estudio</a>
            <a href="fases.php">⚙️ Control de Fases</a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Gestión de Asignaturas de Postgrado</h2>
            <p>Director: <?php echo htmlspecialchars($nombre_director); ?></p>
        </header>

        <?php echo $mensaje; ?>

        <section class="form-section">
            <h3>Registrar Nueva Asignatura</h3>
            <form action="planes.php" method="POST" autocomplete="off">
                
                <div class="form-group">
                    <label for="codigo_materia">Código de la Asignatura</label>
                    <input type="text" name="codigo_materia" id="codigo_materia" placeholder="Ej: INF-401" required>
                </div>

                <div class="form-group">
                    <label for="nombre_materia">Nombre de la Asignatura</label>
                    <input type="text" name="nombre_materia" id="nombre_materia" placeholder="Ej: Gerencia de la Informática" required>
                </div>

                <div class="form-group">
                    <label for="uc_materia">Unidades de Crédito (UC)</label>
                    <input type="number" name="uc_materia" id="uc_materia" min="1" max="10" placeholder="Ej: 3" required>
                </div>

                <button type="submit" name="registrar_materia" class="btn-submit">Guardar Asignatura</button>
            </form>
        </section>

        <section class="table-section">
            <h3>Mallas y Asignaturas Vigentes en el Sistema</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre de la Asignatura</th>
                        <th>Unidades de Crédito (UC)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($materias) > 0): ?>
                        <?php foreach ($materias as $materia): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($materia['codigo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($materia['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($materia['uc']); ?> UC</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No hay asignaturas registradas actualmente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>