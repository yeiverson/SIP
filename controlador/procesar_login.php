<?php
// controlador/procesar_login.php
session_start();

// 1. IMPORTAR LA CONEXIÓN A LA BASE DE DATOS
// Usamos '../' porque este archivo está dentro de 'controlador/' y conexion.php está en 'config/'
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Recibir y limpiar los datos del formulario de login
    // Adaptamos los nombres a los del formulario (tipo_documento, numero_documento y password)
    $tipo_documento   = trim($_POST['tipo_documento']);
    $numero_documento = trim($_POST['numero_documento']);
    $password_login   = $_POST['password'];

    // Validar que los campos no estén vacíos
    if (empty($tipo_documento) || empty($numero_documento) || empty($password_login)) {
        header("Location: ../index.php?error=Por+favor,+complete+todos+los+campos.");
        exit();
    }

    try {
        // 3. Consulta preparada con PDO para buscar al usuario de forma segura
        // Como confirmamos que tu columna es VARCHAR (character varying), buscará perfectamente letras y números de pasaportes
        $sql = "SELECT id, tipo_documento, numero_documento, nombres, apellidos, password_hash, rol_id, sede_id, estatus 
                FROM usuarios 
                WHERE tipo_documento = :tipo AND numero_documento = :doc 
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tipo' => $tipo_documento,
            ':doc'  => $numero_documento
        ]);
        
        $usuario = $stmt->fetch();

        // 4. Verificar si el usuario existe en la base de datos
        if ($usuario) {
            
            // Verificar si la cuenta no está suspendida o inactiva
            if ($usuario['estatus'] !== 'Activo') {
                header("Location: ../index.php?error=Su+cuenta+está+inactiva.+Consulte+a+Control+de+Estudios.");
                exit();
            }

            // 5. Verificar la contraseña usando el hashing seguro de PHP (Bcrypt)
            if (password_verify($password_login, $usuario['password_hash'])) {
                
                // 6. Iniciar las variables globales de la Sesión
                $_SESSION['usuario_id']  = $usuario['id'];
                $_SESSION['identidad']   = $usuario['tipo_documento'] . '-' . $usuario['numero_documento'];
                $_SESSION['nombre_full'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
                $_SESSION['rol']         = (int)$usuario['rol_id'];
                $_SESSION['sede']        = $usuario['sede_id'];
                
                // Token de seguridad básico (CSRF) para formularios internos
                $_SESSION['token_csrf']  = bin2hex(random_bytes(32)); 

                // 7. ENRUTADOR DE ROLES (Redirección automática a su panel)
                // Usamos '../../' en las rutas para salir de 'controlador/' y entrar a 'vistas/'
                switch ($_SESSION['rol']) {
                    case 1:
                        header("Location: ../vistas/admin/dashboard.php");
                        break;
                    case 2:
                        header("Location: ../vistas/coordinador/dashboard.php");
                        break;
                    case 3:
                        header("Location: ../vistas/docente/dashboard.php");
                        break;
                    case 4:
                        header("Location: ../vistas/secretaria/dashboard.php");
                        break;
                    case 5:
                        header("Location: ../vistas/aspirante/dashboard.php");
                        break;
                    case 6:
                        header("Location: ../vistas/estudiante/dashboard.php");
                        break;
                    case 7:
                        header("Location: ../vistas/director/dashboard.php");
                        break;
                    default:
                        session_destroy();
                        header("Location: ../index.php?error=Rol+no+configurado+en+el+sistema.");
                        exit();
                }
                exit(); 

            } else {
                // Contraseña mala
                header("Location: ../index.php?error=Contraseña+incorrecta.");
                exit();
            }
        } else {
            // Usuario no encontrado
            header("Location: ../index.php?error=El+usuario+no+está+registrado.");
            exit();
        }

    } catch (PDOException $e) {
        // En caso de un fallo físico de conexión o consulta en Postgres
        header("Location: ../index.php?error=Error+en+el+servidor+al+procesar+el+ingreso.");
        exit();
    }

} else {
    // Si intentan forzar la URL directa del controlador, los regresa al formulario
    header("Location: ../index.php");
    exit();
}