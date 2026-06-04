<?php
// controlador/cerrar_sesion.php
session_start();
session_unset();
session_destroy();

// Redirige al login con un mensaje limpio
header("Location: ../index.php?error=Sesión+cerrada+correctamente.");
exit();