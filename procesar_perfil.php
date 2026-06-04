<?php

session_start();

require_once 'config.php';
require_once 'procesar.php';
require_once __DIR__ . '/includes/queries_usuarios.php';

if (empty($_SESSION['user_id'])) {
    header('Location: Inicio.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: llenado_de_perfil.php');
    exit;
}

$datos = procesar_datos_desde_post();

$lista_errores = [];

$telefono_trabajo_opcional = function ($valor) use ($telefono_ve) {
    $valor = trim((string) $valor);
    if ($valor === '') {
        return true;
    }
    return $telefono_ve($valor);
};

$errores = [
    'tipoDocumento' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona una opción de tipo de documento',
    ],
    'cedula' => [
        'validar' => $cedula_valida,
        'mensaje' => 'Cédula incorrecta',
    ],
    'primerNombre' => [
        'validar' => $vacio,
        'mensaje' => 'Por favor ingresa tu primer nombre',
    ],
    'segundoNombre' => [
        'validar' => $vacio,
        'mensaje' => 'Por favor ingresa tu segundo nombre',
    ],
    'primerApellido' => [
        'validar' => $vacio,
        'mensaje' => 'Por favor ingresa tu primer apellido',
    ],
    'segundoApellido' => [
        'validar' => $vacio,
        'mensaje' => 'Por favor ingresa tu segundo apellido',
    ],
    'fechaNacimiento' => [
        'validar' => $vacio,
        'mensaje' => 'Indica la fecha de nacimiento',
    ],
    'sexo' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el sexo',
    ],
    'estadoCivil' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el estado civil',
    ],
    'estadoHabitacion' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el estado de habitación',
    ],
    'municipioHabitacion' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el municipio',
    ],
    'parroquiaHabitacion' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona la parroquia',
    ],
    'ciudadHabitacion' => [
        'validar' => $vacio,
        'mensaje' => 'Indica la ciudad o pueblo',
    ],
    'avenidaCalle' => [
        'validar' => $vacio,
        'mensaje' => 'Indica avenida, calle o vereda',
    ],
    'urbanizacionBarrio' => [
        'validar' => $vacio,
        'mensaje' => 'Indica urbanización, barrio o sector',
    ],
    'tipoResidencia' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el tipo de residencia',
    ],
    'residencia' => [
        'validar' => $vacio,
        'mensaje' => 'Indica residencia (casa/edificio)',
    ],
    'telefono' => [
        'validar' => $telefono_ve,
        'mensaje' => 'Por favor ingresa nuevamente el número telefónico fijo (11 dígitos, inicia con 0)',
    ],
    'celular' => [
        'validar' => $telefono_ve,
        'mensaje' => 'Por favor ingresa nuevamente el número celular (11 dígitos, inicia con 0)',
    ],
    'condicion' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona la condición de ingreso',
    ],
    'condicionUsuario' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona la condición del usuario (Civil/Militar)',
    ],
    'tipoInstitucion' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el tipo de institución',
    ],
    'nombreInstitucion' => [
        'validar' => $vacio,
        'mensaje' => 'Indica el nombre de la institución',
    ],
    'antiguedad' => [
        'validar' => $vacio,
        'mensaje' => 'Indica la antigüedad',
    ],
    'telefonoTrabajo' => [
        'validar' => $telefono_trabajo_opcional,
        'mensaje' => 'Teléfono de trabajo inválido (11 dígitos, inicia con 0) o déjelo vacío',
    ],
    'cargo' => [
        'validar' => $vacio,
        'mensaje' => 'Indica el cargo',
    ],
    'trabajaUnefa' => [
        'validar' => $vacio,
        'mensaje' => 'Indica si trabaja en la UNEFA',
    ],
    'areaConocimiento' => [
        'validar' => $vacio,
        'mensaje' => 'Indica el área de conocimiento',
    ],
    'nivelAcademico' => [
        'validar' => $vacio,
        'mensaje' => 'Indica el nivel académico',
    ],
    'universidad' => [
        'validar' => $vacio,
        'mensaje' => 'Indica la universidad',
    ],
    'tituloAcademico' => [
        'validar' => $vacio,
        'mensaje' => 'Indica el título obtenido',
    ],
    'anoGraduacion' => [
        'validar' => $vacio,
        'mensaje' => 'Selecciona el año de graduación',
    ],
    'promedio' => [
        'validar' => $vacio,
        'mensaje' => 'Indica el promedio',
    ],
    'tipoBeca' => [
        'validar' => $vacio,
        'mensaje' => 'Indica si cuenta con beca',
    ],
    'fechaIngresoUnefa' => [
        'validar' => $vacio,
        'mensaje' => 'Indica la fecha de ingreso a la UNEFA',
    ],
];

foreach ($errores as $campo => $array_interno) {
    $recibir = $datos[$campo] ?? '';

    if (!$array_interno['validar']($recibir, $datos)) {
        $lista_errores[$campo] = $array_interno['mensaje'];
    }
}

if (($datos['tipoDocumento'] ?? '') === 'E') {
    if (!$vacio($datos['paisNacimiento'] ?? '')) {
        $lista_errores['paisNacimiento'] = 'Indica el país de nacimiento';
    }
}

if (($datos['tipoResidencia'] ?? '') === 'Apartamento') {
    if (!$vacio($datos['piso'] ?? '')) {
        $lista_errores['piso'] = 'Indica el piso';
    }
    if (!$vacio($datos['apartamento'] ?? '')) {
        $lista_errores['apartamento'] = 'Indica el número de apartamento';
    }
}

$tuLab = $datos['trabajaUnefa'] ?? '';
if ($tuLab === 'No') {
    if (!$vacio($datos['areaTrabajo'] ?? '')) {
        $lista_errores['areaTrabajo'] = 'Indica el área laboral';
    }
    if (!$vacio($datos['dedicacion'] ?? '')) {
        $lista_errores['dedicacion'] = 'Selecciona la dedicación';
    }
} elseif ($tuLab === 'Sí') {
    if (!$vacio($datos['areaUnefa'] ?? '')) {
        $lista_errores['areaUnefa'] = 'Indica el área en la UNEFA';
    }
    if (($datos['areaUnefa'] ?? '') === 'Docente' && !$vacio($datos['dedicacion'] ?? '')) {
        $lista_errores['dedicacion'] = 'Selecciona la dedicación';
    }
}

if (($datos['condicionUsuario'] ?? '') === 'Militar') {
    if (!$vacio($datos['situacionMilitar'] ?? '')) {
        $lista_errores['situacionMilitar'] = 'Indica la situación militar';
    }
    if (!$vacio($datos['componenteMilitar'] ?? '')) {
        $lista_errores['componenteMilitar'] = 'Indica el componente';
    }
    if (!$vacio($datos['gradoMilitar'] ?? '')) {
        $lista_errores['gradoMilitar'] = 'Indica el grado';
    }
}

if (empty($lista_errores)) {
    try {
        $fila = query_usuario_por_id($pdo, $userId);

        if (!$fila) {
            $lista_errores['sesion'] = 'No se encontró el usuario en sesión.';
        } else {
            $cedula_form = preg_replace('/\D/', '', (string) ($datos['cedula'] ?? ''));
            if ((string) $fila['cedula'] !== $cedula_form || $fila['tipo_cedula'] !== ($datos['tipoDocumento'] ?? '')) {
                $lista_errores['cedula'] = 'La cédula o tipo de documento no coincide con tu cuenta.';
            }
        }
    } catch (PDOException $e) {
        error_log('Error al verificar usuario: ' . $e->getMessage());
        $lista_errores['db'] = 'Hubo un problema al verificar tu sesión.';
    }
}

if (!empty($lista_errores)) {
    echo '<h1>Errores de validación:</h1>';
    echo '<ul>';
    foreach ($lista_errores as $campo => $mensaje) {
        $campo_limpio = htmlspecialchars((string) $campo, ENT_QUOTES, 'UTF-8');
        $mensaje_limpio = htmlspecialchars((string) $mensaje, ENT_QUOTES, 'UTF-8');
        echo "<li><strong>{$campo_limpio}:</strong> {$mensaje_limpio}</li>";
    }
    echo '</ul>';
    echo "<a href='llenado_de_perfil.php'>Volver a intentar</a>";
    exit;
}

$nombres = trim((string) $datos['primerNombre'] . ' ' . (string) $datos['segundoNombre']);
$apellidos = trim((string) $datos['primerApellido'] . ' ' . (string) $datos['segundoApellido']);

$celular_limpio = preg_replace('/\D/', '', (string) $datos['celular']);

$paisTxt = ($datos['tipoDocumento'] ?? '') === 'E'
    ? (string) ($datos['paisNacimiento'] ?? '')
    : 'Venezuela';

$esApto = (($datos['tipoResidencia'] ?? '') === 'Apartamento');
$pisoTxt = $esApto ? (string) ($datos['piso'] ?? '') : '—';
$aptoTxt = $esApto ? (string) ($datos['apartamento'] ?? '') : '—';

$obsTxt = trim((string) ($datos['observaciones'] ?? ''));

$militarTxt = ($datos['condicionUsuario'] ?? '') === 'Militar'
    ? "\n" . 'Situación militar: ' . (string) ($datos['situacionMilitar'] ?? '')
        . "\n" . 'Componente: ' . (string) ($datos['componenteMilitar'] ?? '')
        . "\n" . 'Grado: ' . (string) ($datos['gradoMilitar'] ?? '')
    : '';

$direccion = "--- Datos personales ---\n"
    . 'Fecha de nacimiento: ' . (string) $datos['fechaNacimiento'] . "\n"
    . 'Sexo: ' . (string) $datos['sexo'] . "\n"
    . 'País de nacimiento: ' . $paisTxt . "\n"
    . 'Estado civil: ' . (string) $datos['estadoCivil'] . "\n"
    . 'Condición de ingreso: ' . (string) $datos['condicion'] . "\n"
    . 'Condición usuario: ' . (string) ($datos['condicionUsuario'] ?? '') . $militarTxt . "\n\n"
    . "--- Dirección de habitación ---\n"
    . 'Tipo de residencia: ' . (string) $datos['tipoResidencia'] . "\n"
    . 'Estado: ' . (string) $datos['estadoHabitacion'] . "\n"
    . 'Municipio: ' . (string) $datos['municipioHabitacion'] . "\n"
    . 'Parroquia: ' . (string) $datos['parroquiaHabitacion'] . "\n"
    . 'Ciudad/Pueblo: ' . (string) $datos['ciudadHabitacion'] . "\n"
    . 'Avenida/Calle/Vereda: ' . (string) $datos['avenidaCalle'] . "\n"
    . 'Urbanización/Barrio/Sector: ' . (string) $datos['urbanizacionBarrio'] . "\n"
    . 'Residencia: ' . (string) $datos['residencia'] . "\n"
    . 'Piso: ' . $pisoTxt . "\n"
    . 'Nro. apartamento: ' . $aptoTxt . "\n\n"
    . 'Teléfono fijo: ' . (string) $datos['telefono'] . "\n\n"
    . "--- Datos laborales ---\n"
    . 'Tipo de institución: ' . (string) $datos['tipoInstitucion'] . "\n"
    . 'Institución: ' . (string) $datos['nombreInstitucion'] . "\n"
    . 'Antigüedad: ' . (string) $datos['antiguedad'] . "\n"
    . 'Tel. trabajo: ' . (string) ($datos['telefonoTrabajo'] ?? '') . "\n"
    . 'Cargo: ' . (string) $datos['cargo'] . "\n"
    . 'Trabaja UNEFA: ' . (string) $datos['trabajaUnefa'] . "\n"
    . 'Área UNEFA: ' . (string) ($datos['areaUnefa'] ?? '—') . "\n"
    . 'Área trabajo: ' . (string) ($datos['areaTrabajo'] ?? '—') . "\n"
    . 'Dedicación: ' . (string) ($datos['dedicacion'] ?? '—') . "\n\n"
    . "--- Datos académicos ---\n"
    . 'Área conocimiento: ' . (string) $datos['areaConocimiento'] . "\n"
    . 'Nivel académico: ' . (string) $datos['nivelAcademico'] . "\n"
    . 'Universidad: ' . (string) $datos['universidad'] . "\n"
    . 'Título: ' . (string) $datos['tituloAcademico'] . "\n"
    . 'Año graduación: ' . (string) $datos['anoGraduacion'] . "\n"
    . 'Promedio: ' . (string) $datos['promedio'] . "\n\n"
    . "--- Otros datos ---\n"
    . 'Beca: ' . (string) $datos['tipoBeca'] . "\n"
    . 'Fecha ingreso UNEFA: ' . (string) $datos['fechaIngresoUnefa'] . "\n"
    . 'Observaciones: ' . ($obsTxt !== '' ? $obsTxt : '—');

try {
    query_actualizar_perfil_usuario($pdo, $userId, [
        'nombres'   => $nombres,
        'apellidos' => $apellidos,
        'telefono'  => $celular_limpio,
        'direccion' => $direccion,
    ]);

    header('Location: Inicio.php?perfil=guardado');
    exit;
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23505') {
        $mensaje_sql = $e->getMessage();
        if (stripos($mensaje_sql, 'telefono') !== false) {
            echo '<p>Error: Este número de teléfono ya pertenece a otro usuario.</p>';
        } elseif (stripos($mensaje_sql, 'email') !== false || stripos($mensaje_sql, 'correo') !== false) {
            echo '<p>Error: Este correo electrónico ya está registrado.</p>';
        } else {
            echo '<p>Uno de los datos ya se encuentra registrado.</p>';
        }
    } else {
        error_log('Error al guardar perfil: ' . $e->getMessage());
        echo '<p>Hubo un problema al guardar tu perfil.</p>';
    }
    echo "<a href='llenado_de_perfil.php'>Volver</a>";
}
