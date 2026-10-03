<?php
/**
 * Emisor Oficial de Documentos y Reportes Académicos
 * SIP-Postgrado UNEFA
 * Genera Constancias de Estudio, Actas de Calificación, Comprobantes de Inscripción y Dictámenes de Admisión.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

iniciar_sesion_segura();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    exit('Acceso no autorizado.');
}

$tipo = $_GET['tipo'] ?? '';
$usuario_sesion_id = (int)$_SESSION['usuario_id'];
$rol_sesion = (int)($_SESSION['rol'] ?? 0);

// Helper para convertir nota numérica a letras
function nota_a_letras(int $nota): string {
    $letras = [
        0 => 'CERO', 1 => 'UNO', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO',
        5 => 'CINCO', 6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE',
        10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
        15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO',
        19 => 'DIECINUEVE', 20 => 'VEINTE'
    ];
    return $letras[$nota] ?? (string)$nota;
}

// -------------------------------------------------------------
// REPORTE 1: CONSTANCIA DE ESTUDIOS / INSCRIPCIÓN
// -------------------------------------------------------------
if ($tipo === 'constancia_estudio') {
    $uid = isset($_GET['id']) ? (int)$_GET['id'] : $usuario_sesion_id;
    // Solo el propio estudiante o roles administrativos pueden verla
    if ($uid !== $usuario_sesion_id && !in_array($rol_sesion, [1, 2, 4, 7], true)) {
        http_response_code(403);
        exit('No autorizado para emitir este documento.');
    }

    $stmt = $pdo->prepare("SELECT u.*, s.nombre as sede_nombre, p.nombre as plan_nombre, p.tipo as plan_tipo, p.codigo as plan_codigo
                           FROM usuarios u
                           LEFT JOIN sedes s ON s.id = u.sede_id
                           LEFT JOIN plan_estudios p ON p.id = u.plan_id
                           WHERE u.id = :id");
    $stmt->execute([':id' => $uid]);
    $alumno = $stmt->fetch();

    if (!$alumno) {
        exit('Estudiante no encontrado.');
    }

    // Obtener asignaturas actualmente inscritas o cursadas
    $stmtIns = $pdo->prepare("SELECT asig.codigo, asig.nombre, asig.uc, sec.seccion, i.estatus
                             FROM inscripciones i
                             JOIN secciones sec ON sec.id = i.seccion_id
                             JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                             WHERE i.usuario_id = :id AND i.estatus IN ('Formalizada', 'Por Cancelar')
                             ORDER BY asig.nombre");
    $stmtIns->execute([':id' => $uid]);
    $materias = $stmtIns->fetchAll();

    $codigoVerificacion = strtoupper(substr(md5('SIP-' . $alumno['id'] . '-' . date('Ymd')), 0, 16));
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Constancia de Estudio - <?php echo h($alumno['nombres'] . ' ' . $alumno['apellidos']); ?></title>
        <style>
            body { font-family: 'Times New Roman', serif; margin: 40px; color: #111; line-height: 1.6; }
            .header-report { text-align: center; border-bottom: 2px solid #002147; padding-bottom: 12px; margin-bottom: 30px; }
            .header-report img { height: 75px; float: left; }
            .header-report h4 { margin: 0; font-size: 13px; font-weight: normal; text-transform: uppercase; }
            .header-report h3 { margin: 5px 0 0 0; font-size: 15px; font-weight: bold; color: #002147; }
            .header-report h2 { margin: 15px 0 0 0; font-size: 20px; text-decoration: underline; }
            .content-report { font-size: 15px; text-align: justify; margin: 30px 20px; }
            .materias-table { width: 100%; border-collapse: collapse; margin: 25px 0; font-size: 13px; }
            .materias-table th, .materias-table td { border: 1px solid #333; padding: 6px 10px; text-align: left; }
            .materias-table th { background-color: #f2f2f2; }
            .signatures { margin-top: 60px; display: flex; justify-content: space-around; text-align: center; }
            .sig-box { width: 220px; border-top: 1px solid #111; padding-top: 5px; font-size: 13px; }
            .verification-bar { margin-top: 40px; border-top: 1px dashed #777; padding-top: 10px; font-size: 11px; color: #555; text-align: center; }
            .btn-print { background: #002147; color: white; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer; margin-bottom: 20px; }
            @media print {
                .btn-print { display: none; }
                body { margin: 20px; }
            }
        </style>
    </head>
    <body>
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Guardar en PDF</button>

        <div class="header-report">
            <img src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <h4>República Bolivariana de Venezuela</h4>
            <h4>Ministerio del Poder Popular para la Defensa</h4>
            <h4>Universidad Nacional Experimental Politécnica de la Fuerza Armada Nacional Bolivariana</h4>
            <h3>VICERRECTORADO DE INVESTIGACIÓN, POSTGRADO Y RECREACIÓN</h3>
            <h2>CONSTANCIA DE ESTUDIOS</h2>
        </div>

        <div class="content-report">
            <p>Quien suscribe, la Jefatura de Secretaría de Control de Estudios y Gestión Académica de Postgrado de la <strong>UNEFA (<?php echo h($alumno['sede_nombre'] ?? 'Sede Principal'); ?>)</strong>, por medio de la presente hace constar que:</p>

            <p style="text-indent: 40px; margin: 25px 0;">
                El (La) ciudadano(a): <strong><?php echo h(strtoupper($alumno['nombres'] . ' ' . $alumno['apellidos'])); ?></strong>, titular de la Cédula de Identidad / Documento Nº <strong><?php echo h($alumno['tipo_cedula'] . '-' . $alumno['numero_documento']); ?></strong>, se encuentra formalmente inscrito(a) como <strong>Estudiante Regular</strong> en el programa académico:
            </p>

            <p style="text-align: center; font-size: 17px; font-weight: bold; color: #002147; margin: 20px 0;">
                <?php echo h(strtoupper($alumno['plan_nombre'] ?? 'PROGRAMA DE POSTGRADO')); ?> (<?php echo h($alumno['plan_codigo'] ?? 'SIP-2026'); ?>)
            </p>

            <?php if (count($materias) > 0): ?>
            <p>Cursando actualmente las siguientes unidades curriculares correspondientes al presente período académico:</p>
            <table class="materias-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Código</th>
                        <th style="width: 60%;">Asignatura</th>
                        <th style="width: 10%; text-align: center;">Sec.</th>
                        <th style="width: 15%; text-align: center;">U.C.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalUc = 0;
                    foreach ($materias as $m): 
                        $totalUc += (int)$m['uc'];
                    ?>
                    <tr>
                        <td><?php echo h($m['codigo']); ?></td>
                        <td><?php echo h($m['nombre']); ?></td>
                        <td style="text-align: center;"><?php echo h($m['seccion']); ?></td>
                        <td style="text-align: center;"><?php echo $m['uc']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th colspan="3" style="text-align: right;">Total Unidades de Crédito:</th>
                        <th style="text-align: center;"><?php echo $totalUc; ?> UC</th>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>

            <p>Constancia que se expide a solicitud de la parte interesada, en la ciudad de Caracas, a los <strong><?php echo date('d'); ?></strong> días del mes de <strong><?php echo obtener_meses()[date('n')-1]; ?></strong> del año <strong><?php echo date('Y'); ?></strong>.</p>
        </div>

        <div class="signatures">
            <div class="sig-box">
                <strong>Control de Estudios</strong><br>
                División de Postgrado e Investigación<br>
                UNEFA
            </div>
            <div class="sig-box">
                <strong>Coordinación Académica</strong><br>
                Programa de Postgrado<br>
                UNEFA
            </div>
        </div>

        <div class="verification-bar">
            Código de Verificación Seguro: <strong><?php echo $codigoVerificacion; ?></strong> &middot; Documento Oficial generado por SIP-Postgrado UNEFA
        </div>
    </body>
    </html>
    <?php
    exit;
}

// -------------------------------------------------------------
// REPORTE 2: ACTA OFICIAL DE EVALUACIÓN DEFINITIVA (DOCENTE)
// -------------------------------------------------------------
if ($tipo === 'acta_evaluacion') {
    $seccion_id = (int)($_GET['seccion_id'] ?? 0);
    if (!$seccion_id) {
        exit('Sección requerida.');
    }

    // Consultar datos de la sección
    $stmtSec = $pdo->prepare("SELECT sec.*, asig.nombre as materia_nombre, asig.codigo as materia_codigo, asig.uc,
                                     pl.nombre as plan_nombre, pl.codigo as plan_codigo, s.nombre as sede_nombre,
                                     CONCAT(prof.nombres, ' ', prof.apellidos) as profesor_nombre,
                                     CONCAT(prof.tipo_cedula, '-', prof.numero_documento) as profesor_doc
                              FROM secciones sec
                              JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                              JOIN plan_estudios pl ON pl.id = sec.plan_id
                              JOIN sedes s ON s.id = sec.sede_id
                              LEFT JOIN usuarios prof ON prof.id = sec.profesor_id
                              WHERE sec.id = :id");
    $stmtSec->execute([':id' => $seccion_id]);
    $sec = $stmtSec->fetch();

    if (!$sec) {
        exit('Sección no encontrada.');
    }

    // Permisos: Solo docente asignado o roles administrativos
    if ($sec['profesor_id'] != $usuario_sesion_id && !in_array($rol_sesion, [1, 2, 4, 7], true)) {
        http_response_code(403);
        exit('Acceso denegado a esta acta.');
    }

    // Obtener estudiantes y notas
    $stmtEst = $pdo->prepare("SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos,
                                     an.nota, an.inasistencia, an.estatus as acta_estatus, an.updated_at
                              FROM inscripciones i
                              JOIN usuarios u ON u.id = i.usuario_id
                              LEFT JOIN actas_notas an ON an.seccion_id = i.seccion_id AND an.usuario_id = u.id
                              WHERE i.seccion_id = :sec AND i.estatus = 'Formalizada'
                              ORDER BY u.apellidos, u.nombres");
    $stmtEst->execute([':sec' => $seccion_id]);
    $alumnos = $stmtEst->fetchAll();

    $hashActa = strtoupper(substr(sha1('ACTA-' . $sec['id'] . '-' . count($alumnos)), 0, 16));
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Acta de Evaluación Oficial - <?php echo h($sec['materia_codigo']); ?> Sec. <?php echo h($sec['seccion']); ?></title>
        <style>
            body { font-family: 'Arial', sans-serif; margin: 30px; font-size: 12px; color: #222; }
            .header-acta { text-align: center; border-bottom: 2px solid #002147; padding-bottom: 8px; margin-bottom: 15px; }
            .header-acta img { height: 60px; float: left; }
            .header-acta h4 { margin: 0; font-size: 11px; font-weight: normal; text-transform: uppercase; }
            .header-acta h3 { margin: 3px 0; font-size: 13px; font-weight: bold; color: #002147; }
            .header-acta h2 { margin: 8px 0; font-size: 16px; font-weight: bold; }
            .meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; background: #f9fafb; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; font-size: 11px; }
            .acta-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .acta-table th, .acta-table td { border: 1px solid #444; padding: 5px 8px; }
            .acta-table th { background: #002147; color: white; font-weight: 600; font-size: 11px; }
            .acta-table tr:nth-child(even) { background-color: #f8fafc; }
            .text-center { text-align: center; }
            .signatures-acta { margin-top: 40px; display: flex; justify-content: space-around; text-align: center; }
            .sig-block { width: 220px; border-top: 1px solid #333; padding-top: 4px; font-size: 11px; }
            .btn-print { background: #002147; color: white; border: none; padding: 8px 16px; font-size: 13px; border-radius: 4px; cursor: pointer; margin-bottom: 15px; }
            @media print { .btn-print { display: none; } }
        </style>
    </head>
    <body>
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Acta Oficial</button>

        <div class="header-acta">
            <img src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <h4>República Bolivariana de Venezuela &middot; Ministerio del Poder Popular para la Defensa</h4>
            <h4>Universidad Nacional Experimental Politécnica de la Fuerza Armada Nacional Bolivariana</h4>
            <h3>VICERRECTORADO DE INVESTIGACIÓN, POSTGRADO Y RECREACIÓN</h3>
            <h2>ACTA DE EVALUACIÓN DEFINITIVA DE CALIFICACIONES</h2>
        </div>

        <div class="meta-grid">
            <div><strong>Programa:</strong> <?php echo h($sec['plan_nombre']); ?></div>
            <div><strong>Asignatura:</strong> <?php echo h($sec['materia_codigo'] . ' - ' . $sec['materia_nombre']); ?></div>
            <div><strong>Sección / Aula:</strong> <?php echo h($sec['seccion'] . ' / ' . ($sec['aula'] ?: 'Virtual')); ?></div>
            <div><strong>Profesor:</strong> <?php echo h($sec['profesor_nombre'] ?: 'No asignado'); ?> (<?php echo h($sec['profesor_doc']); ?>)</div>
            <div><strong>Núcleo / Sede:</strong> <?php echo h($sec['sede_nombre']); ?></div>
            <div><strong>U.C. / Período:</strong> <?php echo h($sec['uc'] . ' UC / ' . ($sec['periodo'] ?: '2026-I')); ?></div>
        </div>

        <table class="acta-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Nº</th>
                    <th style="width: 15%;">Cédula / Doc.</th>
                    <th style="width: 40%;">Apellidos y Nombres</th>
                    <th style="width: 10%;" class="text-center">Nota (0-20)</th>
                    <th style="width: 18%;">En Letras</th>
                    <th style="width: 12%;" class="text-center">Resultado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($alumnos) === 0): ?>
                    <tr><td colspan="6" class="text-center">No hay alumnos formalizados en esta sección.</td></tr>
                <?php else: ?>
                    <?php 
                    $i = 1;
                    $aprobados = 0; $reprobados = 0; $inasistentes = 0;
                    foreach ($alumnos as $al):
                        $notaNum = $al['nota'];
                        $isN_S = (bool)$al['inasistencia'];
                        $resultado = 'SIN NOTA';
                        $resultadoLetras = '—';

                        if ($isN_S) {
                            $resultado = 'NO ASISTIÓ';
                            $resultadoLetras = 'INASISTENTE';
                            $inasistentes++;
                        } elseif ($notaNum !== null) {
                            $resultadoLetras = nota_a_letras((int)$notaNum);
                            if ($notaNum >= 14) {
                                $resultado = 'APROBADO';
                                $aprobados++;
                            } else {
                                $resultado = 'REPROBADO';
                                $reprobados++;
                            }
                        }
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td><?php echo h($al['tipo_cedula'] . '-' . $al['numero_documento']); ?></td>
                        <td><strong><?php echo h(strtoupper($al['apellidos'] . ', ' . $al['nombres'])); ?></strong></td>
                        <td class="text-center" style="font-size: 13px; font-weight: bold;"><?php echo $isN_S ? 'N/S' : ($notaNum !== null ? sprintf('%02d', $notaNum) : '—'); ?></td>
                        <td><?php echo h($resultadoLetras); ?></td>
                        <td class="text-center" style="font-weight: bold; color: <?php echo $resultado === 'APROBADO' ? '#15803d' : ($resultado === 'REPROBADO' ? '#b91c1c' : '#4b5563'); ?>;">
                            <?php echo $resultado; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 15px; font-size: 11px;">
            <strong>Estadísticas del Acta:</strong> Total Cursantes: <?php echo count($alumnos); ?> &middot; 
            Aprobados: <span style="color:#15803d; font-weight:bold;"><?php echo $aprobados; ?></span> &middot; 
            Reprobados: <span style="color:#b91c1c; font-weight:bold;"><?php echo $reprobados; ?></span> &middot; 
            Inasistentes: <?php echo $inasistentes; ?>
        </div>

        <div class="signatures-acta">
            <div class="sig-block">
                <strong><?php echo h($sec['profesor_nombre'] ?: 'Docente Asignado'); ?></strong><br>
                Firma del Profesor de la Asignatura
            </div>
            <div class="sig-block">
                <strong>Coordinación de Programa</strong><br>
                Firma y Sello Académico
            </div>
            <div class="sig-block">
                <strong>Secretaría de Control de Estudios</strong><br>
                Revisión y Registro Final
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #777; border-top: 1px dashed #aaa; padding-top: 6px;">
            Hash de Seguridad del Acta: <strong><?php echo $hashActa; ?></strong> &middot; Emitido: <?php echo date('d/m/Y H:i'); ?> &middot; Sistema Integral de Postgrado UNEFA
        </div>
    </body>
    </html>
    <?php
    exit;
}

// -------------------------------------------------------------
// REPORTE 3: COMPROBANTE OFICIAL DE INSCRIPCIÓN Y PAGOS
// -------------------------------------------------------------
if ($tipo === 'comprobante_inscripcion') {
    $uid = isset($_GET['id']) ? (int)$_GET['id'] : $usuario_sesion_id;
    if ($uid !== $usuario_sesion_id && !in_array($rol_sesion, [1, 2, 4, 7], true)) {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    $stmt = $pdo->prepare("SELECT u.*, s.nombre as sede_nombre, p.nombre as plan_nombre, p.codigo as plan_codigo
                           FROM usuarios u
                           LEFT JOIN sedes s ON s.id = u.sede_id
                           LEFT JOIN plan_estudios p ON p.id = u.plan_id
                           WHERE u.id = :id");
    $stmt->execute([':id' => $uid]);
    $alumno = $stmt->fetch();

    $stmtIns = $pdo->prepare("SELECT i.*, sec.seccion, sec.aula, asig.codigo, asig.nombre, asig.uc,
                                     CONCAT(prof.nombres, ' ', prof.apellidos) as docente
                             FROM inscripciones i
                             JOIN secciones sec ON sec.id = i.seccion_id
                             JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                             LEFT JOIN usuarios prof ON prof.id = sec.profesor_id
                             WHERE i.usuario_id = :id
                             ORDER BY asig.nombre");
    $stmtIns->execute([':id' => $uid]);
    $materias = $stmtIns->fetchAll();

    // Pagos registrados
    $stmtPagos = $pdo->prepare("SELECT * FROM pagos WHERE usuario_id = :id ORDER BY created_at DESC");
    $stmtPagos->execute([':id' => $uid]);
    $pagos = $stmtPagos->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Comprobante de Inscripción - <?php echo h($alumno['nombres'] . ' ' . $alumno['apellidos']); ?></title>
        <style>
            body { font-family: 'Arial', sans-serif; margin: 30px; font-size: 12px; color: #222; }
            .header-comp { text-align: center; border-bottom: 2px solid #002147; padding-bottom: 8px; margin-bottom: 15px; }
            .header-comp img { height: 55px; float: left; }
            .header-comp h4 { margin: 0; font-size: 11px; font-weight: normal; text-transform: uppercase; }
            .header-comp h3 { margin: 3px 0; font-size: 13px; font-weight: bold; color: #002147; }
            .header-comp h2 { margin: 8px 0; font-size: 16px; font-weight: bold; }
            .info-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 6px; margin-bottom: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
            .tbl { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
            .tbl th, .tbl td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
            .tbl th { background: #002147; color: white; font-size: 11px; }
            .btn-print { background: #002147; color: white; border: none; padding: 8px 16px; font-size: 13px; border-radius: 4px; cursor: pointer; margin-bottom: 15px; }
            @media print { .btn-print { display: none; } }
        </style>
    </head>
    <body>
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Comprobante</button>

        <div class="header-comp">
            <img src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <h4>República Bolivariana de Venezuela &middot; Vicerrectorado de Investigación y Postgrado</h4>
            <h3>SIP-POSTGRADO UNEFA &middot; <?php echo h(strtoupper($alumno['sede_nombre'] ?? 'SEDE CENTRAL')); ?></h3>
            <h2>COMPROBANTE OFICIAL DE INSCRIPCIÓN ACADÉMICA</h2>
        </div>

        <div class="info-box">
            <div><strong>Estudiante:</strong> <?php echo h($alumno['nombres'] . ' ' . $alumno['apellidos']); ?></div>
            <div><strong>Documento:</strong> <?php echo h($alumno['tipo_cedula'] . '-' . $alumno['numero_documento']); ?></div>
            <div><strong>Programa:</strong> <?php echo h($alumno['plan_nombre'] ?? 'Sin asignar'); ?></div>
            <div><strong>Fecha Emisión:</strong> <?php echo date('d/m/Y H:i'); ?></div>
        </div>

        <h4>Asignaturas Registradas</h4>
        <table class="tbl">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Asignatura</th>
                    <th style="text-align: center;">Sec.</th>
                    <th style="text-align: center;">U.C.</th>
                    <th>Docente</th>
                    <th style="text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totUc = 0;
                foreach ($materias as $m): 
                    $totUc += (int)$m['uc'];
                ?>
                <tr>
                    <td><strong><?php echo h($m['codigo']); ?></strong></td>
                    <td><?php echo h($m['nombre']); ?></td>
                    <td style="text-align: center;"><?php echo h($m['seccion']); ?></td>
                    <td style="text-align: center;"><?php echo $m['uc']; ?></td>
                    <td><?php echo h($m['docente'] ?: 'Por asignar'); ?></td>
                    <td style="text-align: center;"><strong><?php echo h($m['estatus']); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3" style="text-align: right;">Total Unidades de Crédito:</th>
                    <th style="text-align: center;"><?php echo $totUc; ?> UC</th>
                    <th colspan="2"></th>
                </tr>
            </tbody>
        </table>

        <?php if (count($pagos) > 0): ?>
        <h4>Registro de Pagos y Aranceles</h4>
        <table class="tbl">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Banco</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagos as $p): ?>
                <tr>
                    <td><strong><?php echo h($p['referencia'] ?: ($p['referencia_bancaria'] ?? '—')); ?></strong></td>
                    <td><?php echo h($p['banco'] ?: 'Transferencia'); ?></td>
                    <td>Bs. <?php echo number_format((float)$p['monto'], 2, ',', '.'); ?></td>
                    <td><?php echo formatear_fecha($p['fecha_pago'] ?? $p['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div style="margin-top: 40px; display: flex; justify-content: space-around; text-align: center;">
            <div style="width: 200px; border-top: 1px solid #333; padding-top: 5px; font-size: 11px;">
                Firma del Estudiante
            </div>
            <div style="width: 200px; border-top: 1px solid #333; padding-top: 5px; font-size: 11px;">
                Sello Control de Estudios
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Tipo de reporte no reconocido — flujo normal ↓

// -------------------------------------------------------------
// REPORTE CSV: USUARIOS (solo Admin rol_id=1)
// -------------------------------------------------------------
if ($tipo === 'csv_usuarios') {
    if ($rol_sesion !== 1) {
        http_response_code(403);
        exit('Solo el Administrador puede exportar el directorio de usuarios.');
    }

    $busqueda = trim((string)($_GET['q'] ?? ''));
    $where = '';
    $params = [];
    if ($busqueda !== '') {
        $where = "WHERE u.nombres ILIKE :q OR u.apellidos ILIKE :q2 OR u.numero_documento ILIKE :q3 OR u.email ILIKE :q4";
        $p = '%' . $busqueda . '%';
        $params = [':q' => $p, ':q2' => $p, ':q3' => $p, ':q4' => $p];
    }

    $stmt = $pdo->prepare("SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos,
                                  u.email, r.nombre AS rol, s.nombre AS sede, u.estatus, u.estado_aspirante,
                                  u.created_at
                           FROM usuarios u
                           LEFT JOIN roles r ON r.id = u.rol_id
                           LEFT JOIN sedes s ON s.id = u.sede_id
                           $where
                           ORDER BY u.apellidos, u.nombres");
    $stmt->execute($params);
    $filas = $stmt->fetchAll();

    $nombre_archivo = 'sip_usuarios_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    header('Cache-Control: no-store, no-cache');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    // BOM UTF-8 para compatibilidad con Excel
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID', 'Tipo Doc', 'Número Doc', 'Nombres', 'Apellidos', 'Email', 'Rol', 'Sede', 'Estatus', 'Estado Aspirante', 'Registrado']);
    foreach ($filas as $f) {
        fputcsv($out, [
            $f['id'],
            $f['tipo_cedula'],
            $f['numero_documento'],
            $f['nombres'],
            $f['apellidos'],
            $f['email'],
            $f['rol'] ?? '',
            $f['sede'] ?? '',
            $f['estatus'],
            $f['estado_aspirante'],
            $f['created_at'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

// -------------------------------------------------------------
// REPORTE CSV: SECCIONES (solo Admin y Coordinador)
// -------------------------------------------------------------
if ($tipo === 'csv_secciones') {
    if (!in_array($rol_sesion, [1, 2], true)) {
        http_response_code(403);
        exit('No autorizado para exportar secciones.');
    }

    $params = [];
    $where  = ['s.activa = TRUE'];

    if ($rol_sesion === 2) {
        // El coordinador solo ve su sede
        $where[]         = 's.sede_id = :sede';
        $params[':sede'] = (int)($_SESSION['sede_id'] ?? 0);
    }

    $whereSql = 'WHERE ' . implode(' AND ', $where);
    $stmt = $pdo->prepare("SELECT s.id, a.nombre AS asignatura, s.seccion, s.periodo,
                                  u.nombres AS docente_nombres, u.apellidos AS docente_apellidos,
                                  se.nombre AS sede, s.cupo_maximo, s.aula, s.activa
                           FROM secciones s
                           JOIN asignaturas a ON a.codigo = s.asignatura_codigo
                           LEFT JOIN usuarios u ON u.id = s.profesor_id
                           LEFT JOIN sedes se ON se.id = s.sede_id
                           $whereSql
                           ORDER BY s.periodo DESC, a.nombre");
    $stmt->execute($params);
    $filas = $stmt->fetchAll();

    $nombre_archivo = 'sip_secciones_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    header('Cache-Control: no-store, no-cache');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID', 'Asignatura', 'Sección', 'Período', 'Docente', 'Sede', 'Cupo Máximo', 'Aula']);
    foreach ($filas as $f) {
        fputcsv($out, [
            $f['id'],
            $f['asignatura'],
            $f['seccion'],
            $f['periodo'],
            trim($f['docente_nombres'] . ' ' . $f['docente_apellidos']) ?: '(Sin asignar)',
            $f['sede'] ?? '',
            $f['cupo_maximo'],
            $f['aula'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

// Tipo de reporte no reconocido
exit('Tipo de documento no especificado.');
