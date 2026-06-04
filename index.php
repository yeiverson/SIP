<?php
// index.php
session_start();
// Si ya hay sesión, lo enviamos a su dashboard directamente
if (isset($_SESSION['usuario_id'])) {
    // Aquí puedes poner una lógica simple para redirigir según el rol si ya están logueados
    header("Location: vistas/director/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SIP-Postgrado | Iniciar Sesión</title>
    <link rel="stylesheet" href="css/tu_estilo.css">
</head>
<body>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        
        <form action="controlador/procesar_login.php" method="POST">
            
            <label>Tipo de Documento:</label>
            <select name="tipo_documento" required>
                <option value="V">V</option>
                <option value="E">E</option>
                <option value="P">P (Pasaporte)</option>
            </select>

            <label>Número de Documento:</label>
            <input type="text" name="numero_documento" required placeholder="Ej: 12345678">

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Ingresar al Sistema</button>
        </form>

        <?php
        // Mostrar error si existe uno en la URL
        if (isset($_GET['error'])) {
            echo "<p style='color:red; text-align:center;'>" . htmlspecialchars($_GET['error']) . "</p>";
        }
        ?>
    </div>

</body>
</html>