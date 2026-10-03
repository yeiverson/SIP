<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(4);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

iniciar_sesion_segura();

$secretaria_id = (int)$_SESSION['usuario_id'];
$nombre_secre  = $_SESSION['nombre_full'];
$sede_id       = (int)($_SESSION['sede_id'] ?? 0);
$mensaje       = '';

// Obtener información de la sede
$sede_info = null;
if ($sede_id > 0) {
    $info_sede = $pdo->prepare("SELECT * FROM sedes WHERE id = :id");
    $info_sede->execute([':id' => $sede_id]);
    $sede_info = $info_sede->fetch();
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $mensaje = alerta_error('Token de seguridad inválido. Recargue la página.');
    } else {
        // 1. Admitir aspirante -> Promover a Estudiante Regular (rol 6)
        if (isset($_POST['admitir_aspirante'])) {
            $uid = (int)$_POST['usuario_id'];
            $asig_sede = !empty($_POST['asignar_sede_id']) ? (int)$_POST['asignar_sede_id'] : ($sede_id ?: null);
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE usuarios SET rol_id = 6, estado_aspirante = 'Admitido', sede_id = COALESCE(:sede, sede_id) WHERE id = :id AND rol_id = 5");
                $stmt->execute([':id' => $uid, ':sede' => $asig_sede]);
                if ($stmt->rowCount() > 0) {
                    $stmt2 = $pdo->prepare("UPDATE aspirante_documentos SET verificado = true WHERE usuario_id = :uid");
                    $stmt2->execute([':uid' => $uid]);
                    $pdo->commit();
                    registrar_log($pdo, 'Admitir aspirante', 'usuarios', $uid, "Aspirante ID $uid promovido a Estudiante Regular");
                    $mensaje = alerta_success('Aspirante admitido formalmente como Estudiante Regular.');
                } else {
                    $pdo->rollBack();
                    $mensaje = alerta_error('El usuario no es un aspirante válido o ya fue admitido.');
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = alerta_error('Error al procesar admisión: ' . $e->getMessage());
            }
        }
        // 2. Colocar observaciones al expediente del aspirante
        elseif (isset($_POST['observar_aspirante'])) {
            $uid = (int)$_POST['usuario_id'];
            $obs = trim($_POST['observacion'] ?? '');
            if ($obs) {
                $stmt = $pdo->prepare("UPDATE usuarios SET estado_aspirante = 'Con Observaciones' WHERE id = :id AND rol_id = 5");
                $stmt->execute([':id' => $uid]);
                registrar_log($pdo, 'Observar aspirante', 'usuarios', $uid, "Observación: $obs");
                $mensaje = alerta_warning("Se registraron observaciones para el aspirante. Se le notificará corregir sus recaudos.");
            } else {
                $mensaje = alerta_error("Ingrese el detalle de las observaciones para el aspirante.");
            }
        }
        // 3. Validar pago en taquilla virtual y formalizar inscripciones
        elseif (isset($_POST['validar_pago'])) {
            $usuario_id = (int)$_POST['usuario_id'];
            $banco = trim($_POST['banco'] ?? 'Transferencia Bancaria');
            $referencia = trim($_POST['referencia'] ?? '');
            $monto = (float)($_POST['monto'] ?? 0);
            $fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d');

            if ($referencia && $monto > 0) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO pagos (usuario_id, banco, referencia, monto, fecha_pago, secretaria_id)
                            VALUES (:uid, :banco, :ref, :monto, :fp, :sec) RETURNING id");
                    $stmt->execute([
                        ':uid'   => $usuario_id,
                        ':banco' => $banco,
                        ':ref'   => $referencia,
                        ':monto' => $monto,
                        ':fp'    => $fecha_pago,
                        ':sec'   => $secretaria_id,
                    ]);
                    $pago_id = $stmt->fetchColumn();

                    // Formalizar todas las inscripciones en estado 'Por Cancelar'
                    $stmt = $pdo->prepare("UPDATE inscripciones SET estatus = 'Formalizada', updated_at = NOW()
                            WHERE usuario_id = :uid AND estatus = 'Por Cancelar'");
                    $stmt->execute([':uid' => $usuario_id]);
                    $afectadas = $stmt->rowCount();

                    $pdo->commit();
                    registrar_log($pdo, 'Validar pago inscripción', 'pagos', $pago_id, "Ref: $referencia, Monto: $monto, Materias formalizadas: $afectadas");
                    $mensaje = alerta_success("Pago de Bs. " . number_format($monto, 2, ',', '.') . " validado exitosamente. $afectadas asignatura(s) formalizada(s).");
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $mensaje = ($e->getCode() === '23505')
                        ? alerta_error("La referencia bancaria '$referencia' ya fue registrada previamente.")
                        : alerta_error('Error al procesar el pago: ' . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error("Indique una referencia bancaria y un monto válido mayor a cero.");
            }
        }
        // 4. Eliminar asignatura inscrita y generar crédito resguardado
        elseif (isset($_POST['eliminar_inscripcion'])) {
            $insc_id = (int)$_POST['inscripcion_id'];
            $justificacion = trim($_POST['justificacion'] ?? '');
            if ($justificacion) {
                try {
                    $pdo->beginTransaction();
                    $stmtInfo = $pdo->prepare("SELECT i.*, asig.uc FROM inscripciones i 
                                               JOIN secciones sec ON sec.id = i.seccion_id 
                                               JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo 
                                               WHERE i.id = :id");
                    $stmtInfo->execute([':id' => $insc_id]);
                    $data = $stmtInfo->fetch();

                    if ($data) {
                        $stmt = $pdo->prepare("UPDATE inscripciones SET estatus = 'Eliminada', updated_at = NOW() WHERE id = :id");
                        $stmt->execute([':id' => $insc_id]);

                        if ($data['estatus'] === 'Formalizada') {
                            $stmtCred = $pdo->prepare("INSERT INTO creditos_resguardados (usuario_id, sede_origen_id, uc_resguardadas, motivo, estatus)
                                                       VALUES (:uid, :sede, :uc, 'Eliminacion', 'Activo')");
                            $stmtCred->execute([
                                ':uid'  => $data['usuario_id'],
                                ':sede' => $sede_id ?: 1,
                                ':uc'   => $data['uc'],
                            ]);
                        }
                        $pdo->commit();
                        registrar_log($pdo, 'Eliminar inscripción', 'inscripciones', $insc_id, $justificacion);
                        $mensaje = alerta_success('Asignatura retirada. Se ha generado el resguardo de créditos correspondiente.');
                    } else {
                        $pdo->rollBack();
                        $mensaje = alerta_error('Registro de inscripción no encontrado.');
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $mensaje = alerta_error('Error al retirar asignatura: ' . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error('Debe indicar el motivo o justificación institucional del retiro.');
            }
        }
    }
}

// Consultas del panel
// 1. Aspirantes en revisión (sede actual o todas si la secretaria es central)
if ($sede_id > 0) {
    $stmtAsp = $pdo->prepare("SELECT u.*, s.nombre as sede_nombre, pl.nombre as plan_nombre,
                                     (SELECT COUNT(*) FROM aspirante_documentos ad WHERE ad.usuario_id = u.id) as docs_subidos,
                                     (SELECT COUNT(*) FROM aspirante_documentos ad WHERE ad.usuario_id = u.id AND ad.verificado = true) as docs_verificados
                              FROM usuarios u
                              LEFT JOIN sedes s ON s.id = u.sede_id
                              LEFT JOIN plan_estudios pl ON pl.id = u.plan_id
                              WHERE u.rol_id = 5 AND (u.sede_id = :sede OR u.sede_id IS NULL)
                              ORDER BY u.id DESC");
    $stmtAsp->execute([':sede' => $sede_id]);
} else {
    $stmtAsp = $pdo->query("SELECT u.*, s.nombre as sede_nombre, pl.nombre as plan_nombre,
                                   (SELECT COUNT(*) FROM aspirante_documentos ad WHERE ad.usuario_id = u.id) as docs_subidos,
                                   (SELECT COUNT(*) FROM aspirante_documentos ad WHERE ad.usuario_id = u.id AND ad.verificado = true) as docs_verificados
                            FROM usuarios u
                            LEFT JOIN sedes s ON s.id = u.sede_id
                            LEFT JOIN plan_estudios pl ON pl.id = u.plan_id
                            WHERE u.rol_id = 5
                            ORDER BY u.id DESC");
}
$aspirantes = $stmtAsp->fetchAll();

// 2. Estudiantes con materias "Por Cancelar"
$stmtPorCanc = $pdo->prepare("SELECT DISTINCT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos, u.email,
                                     COUNT(i.id) as materias_pendientes,
                                     COALESCE(SUM(asig.uc), 0) as uc_pendientes
                              FROM usuarios u
                              JOIN inscripciones i ON i.usuario_id = u.id
                              JOIN secciones sec ON sec.id = i.seccion_id
                              JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                              WHERE i.estatus = 'Por Cancelar'
                                AND (:sede = 0 OR sec.sede_id = :sede)
                              GROUP BY u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos, u.email
                              ORDER BY u.apellidos");
$stmtPorCanc->execute([':sede' => $sede_id]);
$por_cancelar = $stmtPorCanc->fetchAll();

// 3. Maestro de Estudiantes Regulares
$stmtRegulares = $pdo->prepare("SELECT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos, u.email,
                                       s.nombre as sede_nombre, pl.nombre as plan_nombre,
                                       COUNT(DISTINCT i.id) as materias_inscritas
                                FROM usuarios u
                                LEFT JOIN sedes s ON s.id = u.sede_id
                                LEFT JOIN plan_estudios pl ON pl.id = u.plan_id
                                LEFT JOIN inscripciones i ON i.usuario_id = u.id AND i.estatus = 'Formalizada'
                                WHERE u.rol_id = 6
                                  AND (:sede = 0 OR u.sede_id = :sede)
                                GROUP BY u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos, u.email, s.nombre, pl.nombre
                                ORDER BY u.apellidos LIMIT 80");
$stmtRegulares->execute([':sede' => $sede_id]);
$estudiantes_regulares = $stmtRegulares->fetchAll();

$sedes_todas = $pdo->query("SELECT id, nombre FROM sedes ORDER BY nombre")->fetchAll();

$titulo = 'Panel Secretaría de Control de Estudios';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">Control de Estudios</span>
            </div>
            <p><?php echo h($nombre_secre); ?></p>
            <p><small><?php echo h($sede_info['nombre'] ?? 'Sede Central'); ?></small></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Visión General</span></a>
            <a href="#modulo-admisiones" data-modulo="admisiones"><span>📁 Admisiones Digitales (<?php echo count($aspirantes); ?>)</span></a>
            <a href="#modulo-taquilla" data-modulo="taquilla"><span>💰 Taquilla y Pagos (<?php echo count($por_cancelar); ?>)</span></a>
            <a href="#modulo-estudiantes" data-modulo="estudiantes"><span>🎓 Maestro de Estudiantes</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div>
                <h2>Control de Estudios y Taquilla Universitaria</h2>
                <p>Sede: <strong><?php echo h($sede_info['nombre'] ?? 'Central'); ?></strong> &middot; <?php echo fecha_hoy_formateada(); ?></p>
            </div>
            <div>
                <span class="badge badge-navy" style="font-size:0.85rem;padding:6px 14px;">🏛️ ATENCIÓN ACADÉMICA</span>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <!-- KPI STATS -->
        <div class="stats-grid">
            <div class="stat-card gold">
                <div class="stat-icon">📑</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($aspirantes); ?></span>
                    <span class="stat-lbl">Expedientes en Revisión</span>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⏳</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($por_cancelar); ?></span>
                    <span class="stat-lbl">Inscripciones por Pagar</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">🎓</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo count($estudiantes_regulares); ?></span>
                    <span class="stat-lbl">Estudiantes Activos</span>
                </div>
            </div>
        </div>

        <!-- ===== MÓDULO 1: ADMISIONES DIGITALES ===== -->
        <section id="modulo-admisiones" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📁 Evaluación de Expedientes de Postgrado</h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="toolbar-actions">
                        <div class="search-input-wrapper">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="filtro-aspirantes" placeholder="Filtrar por cédula, nombre o programa..." onkeyup="filtrarTabla('filtro-aspirantes', 'tabla-aspirantes')">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="tabla-aspirantes">
                            <thead>
                                <tr>
                                    <th>Cédula / Pasaporte</th>
                                    <th>Aspirante</th>
                                    <th>Programa Deseado</th>
                                    <th>Recaudos Subidos</th>
                                    <th>Estado Actual</th>
                                    <th style="text-align:center;">Acción de Secretaría</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($aspirantes) === 0): ?>
                                <tr><td colspan="6" class="text-center">No hay postulaciones en espera en esta sede.</td></tr>
                                <?php else: ?>
                                <?php foreach ($aspirantes as $asp): ?>
                                <tr>
                                    <td><strong><?php echo h($asp['tipo_cedula'] . '-' . $asp['numero_documento']); ?></strong></td>
                                    <td>
                                        <strong><?php echo h($asp['nombres'] . ' ' . $asp['apellidos']); ?></strong><br>
                                        <small style="color:#666;"><?php echo h($asp['email']); ?></small>
                                    </td>
                                    <td><?php echo h($asp['plan_nombre'] ?? 'Maestría / Doctorado'); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo $asp['docs_subidos']; ?> documentos
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($asp['estado_aspirante'] === 'Admitido'): ?>
                                            <span class="badge badge-success">🟢 Admitido</span>
                                        <?php elseif ($asp['estado_aspirante'] === 'Con Observaciones'): ?>
                                            <span class="badge badge-warning">🟡 Observado</span>
                                        <?php else: ?>
                                            <span class="badge badge-planar">🔵 En Revisión</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                            <form method="POST" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="admitir_aspirante" value="1">
                                                <input type="hidden" name="usuario_id" value="<?php echo $asp['id']; ?>">
                                                <button type="submit" class="btn-success btn-sm" onclick="return confirm('¿Aprobar admisión y otorgar estatus de Estudiante Regular?')">
                                                    ✅ Admitir
                                                </button>
                                            </form>
                                            <button type="button" class="btn-outline btn-sm" onclick="abrirObservacion(<?php echo $asp['id']; ?>, '<?php echo h($asp['nombres']); ?>')">
                                                ⚠️ Observar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO 2: TAQUILLA VIRTUAL DE PAGOS ===== -->
        <section id="modulo-taquilla" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>💰 Taquilla Virtual &middot; Formalización de Pagos e Inscripciones</h3>
                </div>
                <div class="dashboard-card-body">
                    <p style="color:#666;font-size:0.85rem;margin-bottom:16px;">
                        Verifique la referencia bancaria del estudiante para validar el arancel de postgrado y formalizar las asignaturas seleccionadas.
                    </p>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Estudiante</th>
                                    <th>Asignaturas Pendientes</th>
                                    <th>Total UC</th>
                                    <th style="text-align:center;">Procesar Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($por_cancelar) === 0): ?>
                                <tr><td colspan="5" class="text-center">No hay inscripciones pendientes de pago.</td></tr>
                                <?php else: ?>
                                <?php foreach ($por_cancelar as $pc): ?>
                                <tr>
                                    <td><strong><?php echo h($pc['tipo_cedula'] . '-' . $pc['numero_documento']); ?></strong></td>
                                    <td>
                                        <?php echo h($pc['nombres'] . ' ' . $pc['apellidos']); ?><br>
                                        <small style="color:#666;"><?php echo h($pc['email']); ?></small>
                                    </td>
                                    <td><span class="badge badge-warning"><?php echo $pc['materias_pendientes']; ?> materia(s)</span></td>
                                    <td><strong><?php echo $pc['uc_pendientes']; ?> UC</strong></td>
                                    <td style="text-align:center;">
                                        <button class="btn-primary btn-sm" onclick="abrirModalPago(<?php echo $pc['id']; ?>, '<?php echo h($pc['nombres'] . ' ' . $pc['apellidos']); ?>')">
                                            💵 Validar Pago
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO 3: MAESTRO DE ESTUDIANTES Y CONSTANCIAS ===== -->
        <section id="modulo-estudiantes" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>🎓 Maestro de Estudiantes y Emisión de Documentos</h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="toolbar-actions">
                        <div class="search-input-wrapper">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="filtro-regulares" placeholder="Buscar estudiante por documento o nombre..." onkeyup="filtrarTabla('filtro-regulares', 'tabla-regulares')">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="tabla-regulares">
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Estudiante</th>
                                    <th>Programa Académico</th>
                                    <th>Sede</th>
                                    <th>Materias Formalizadas</th>
                                    <th style="text-align:center;">Certificados Oficiales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes_regulares as $est): ?>
                                <tr>
                                    <td><strong><?php echo h($est['tipo_cedula'] . '-' . $est['numero_documento']); ?></strong></td>
                                    <td><?php echo h($est['nombres'] . ' ' . $est['apellidos']); ?></td>
                                    <td><?php echo h($est['plan_nombre'] ?? 'Postgrado'); ?></td>
                                    <td><?php echo h($est['sede_nombre'] ?? 'Central'); ?></td>
                                    <td><span class="badge badge-success"><?php echo $est['materias_inscritas']; ?> inscrita(s)</span></td>
                                    <td style="text-align:center;">
                                        <a href="../../controlador/reportes.php?tipo=constancia_estudio&id=<?php echo $est['id']; ?>" target="_blank" class="btn-print-doc btn-sm">
                                            📜 Constancia de Estudio
                                        </a>
                                        <a href="../../controlador/reportes.php?tipo=comprobante_inscripcion&id=<?php echo $est['id']; ?>" target="_blank" class="btn-outline btn-sm" style="margin-left:4px;">
                                            📑 Comprobante
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- MODAL VALIDAR PAGO -->
<div id="modal-pago" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modal-pago-titulo">Validar Pago de Arancel</h3>
            <button class="modal-close" onclick="cerrarModalPago()">&times;</button>
        </div>
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="validar_pago" value="1">
            <input type="hidden" name="usuario_id" id="modal-pago-uid" value="">
            <div class="modal-body">
                <div class="input-group" style="margin-bottom:12px;">
                    <label>Banco Emisor:</label>
                    <select name="banco" required>
                        <option value="Banco de Venezuela">Banco de Venezuela</option>
                        <option value="Banco Bicentenario">Banco Bicentenario</option>
                        <option value="Banco Mercantil">Banco Mercantil</option>
                        <option value="Banesco">Banesco</option>
                        <option value="Banco Provincial">Banco Provincial</option>
                        <option value="Pago Móvil Interbancario">Pago Móvil Interbancario</option>
                    </select>
                </div>
                <div class="input-group" style="margin-bottom:12px;">
                    <label>Número de Referencia Bancaria:</label>
                    <input type="text" name="referencia" placeholder="Ej: 98765432" required>
                </div>
                <div class="form-grid-2">
                    <div class="input-group">
                        <label>Monto Pagado (Bs.):</label>
                        <input type="number" step="0.01" name="monto" placeholder="0.00" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha de la Transacción:</label>
                        <input type="date" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="cerrarModalPago()">Cancelar</button>
                <button type="submit" class="btn-success">✅ Confirmar y Formalizar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL REGISTRAR OBSERVACIÓN -->
<div id="modal-obs" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Registrar Observación a Recaudos</h3>
            <button class="modal-close" onclick="cerrarModalObs()">&times;</button>
        </div>
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="observar_aspirante" value="1">
            <input type="hidden" name="usuario_id" id="modal-obs-uid" value="">
            <div class="modal-body">
                <div class="input-group">
                    <label>Detalle de observaciones para el aspirante:</label>
                    <textarea name="observacion" rows="4" placeholder="Ej: El título universitario adjunto no es legible. Adjunte copia certificada en formato PDF." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="cerrarModalObs()">Cancelar</button>
                <button type="submit" class="btn-primary">Enviar Observación</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalPago(uid, nombre) {
    document.getElementById('modal-pago-uid').value = uid;
    document.getElementById('modal-pago-titulo').textContent = 'Validar Pago: ' + nombre;
    document.getElementById('modal-pago').classList.add('active');
}
function cerrarModalPago() {
    document.getElementById('modal-pago').classList.remove('active');
}
function abrirObservacion(uid, nombre) {
    document.getElementById('modal-obs-uid').value = uid;
    document.getElementById('modal-obs').classList.add('active');
}
function cerrarModalObs() {
    document.getElementById('modal-obs').classList.remove('active');
}
function filtrarTabla(inputId, tableId) {
    const query = document.getElementById(inputId).value.toLowerCase();
    const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
    rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        r.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
