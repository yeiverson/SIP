<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(4);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$secretaria_id = $_SESSION['usuario_id'];
$nombre_secre = $_SESSION['nombre_full'];
$sede_id = $_SESSION['sede_id'];
$mensaje = '';

// Obtener sede
$info_sede = $pdo->prepare("SELECT * FROM sedes WHERE id = :id");
$info_sede->execute([':id' => $sede_id]);
$sede_info = $info_sede->fetch();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Admitir aspirante
    if (isset($_POST['admitir_aspirante'])) {
        $uid = (int)$_POST['usuario_id'];
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE usuarios SET rol_id = 6, estado_aspirante = 'Admitido' WHERE id = :id AND rol_id = 5");
            $stmt->execute([':id' => $uid]);
            if ($stmt->rowCount() > 0) {
                $stmt2 = $pdo->prepare("UPDATE aspirante_documentos SET verificado = true WHERE usuario_id = :uid AND tipo IN ('Cedula','Pasaporte','Titulo')");
                $stmt2->execute([':uid' => $uid]);
                $pdo->commit();
                registrar_log($pdo, 'Admitir aspirante', 'usuarios', $uid);
                $mensaje = alerta_success('Aspirante admitido como Estudiante Regular.');
            } else {
                $pdo->rollBack();
                $mensaje = alerta_error('El usuario no es un aspirante válido.');
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = alerta_error('Error al procesar admisión.');
        }
    }
    // Validar pago
    elseif (isset($_POST['validar_pago'])) {
        $usuario_id = (int)$_POST['usuario_id'];
        $banco = trim($_POST['banco']);
        $referencia = trim($_POST['referencia']);
        $monto = (float)$_POST['monto'];
        $fecha_pago = $_POST['fecha_pago'];

        if ($banco && $referencia && $monto > 0) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO pagos (usuario_id, banco, referencia, monto, fecha_pago, secretaria_id)
                        VALUES (:uid, :banco, :ref, :monto, :fp, :sec)");
                $stmt->execute([
                    ':uid' => $usuario_id, ':banco' => $banco, ':ref' => $referencia,
                    ':monto' => $monto, ':fp' => $fecha_pago, ':sec' => $secretaria_id,
                ]);
                // Formalizar inscripciones
                $stmt = $pdo->prepare("UPDATE inscripciones SET estatus = 'Formalizada', updated_at = NOW()
                        WHERE usuario_id = :uid AND estatus = 'Por Cancelar'");
                $stmt->execute([':uid' => $usuario_id]);
                $pdo->commit();
                registrar_log($pdo, 'Validar pago inscripción', 'pagos', $usuario_id, "Ref: $referencia, Monto: $monto");
                $mensaje = alerta_success('Pago validado. Inscripciones formalizadas.');
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje = $e->getCode() == '23505' ? alerta_error("La referencia '$referencia' ya fue registrada.") : alerta_error('Error al procesar pago.');
            }
        }
    }
    // Eliminar asignatura (con justificación)
    elseif (isset($_POST['eliminar_inscripcion'])) {
        $insc_id = (int)$_POST['inscripcion_id'];
        $justificacion = trim($_POST['justificacion']);
        if ($justificacion) {
            $stmt = $pdo->prepare("UPDATE inscripciones SET estatus = 'Eliminada', updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $insc_id]);
            if ($stmt->rowCount() > 0) {
                // Si estaba formalizada, crear crédito resguardado
                $insc = $pdo->prepare("SELECT i.*, sec.uc FROM inscripciones i JOIN secciones sec ON sec.id = i.seccion_id WHERE i.id = :id");
                $insc->execute([':id' => $insc_id]);
                $data = $insc->fetch();
                if ($data && $data['estatus'] === 'Eliminada') {
                    $stmt = $pdo->prepare("INSERT INTO creditos_resguardados (usuario_id, sede_origen_id, uc_resguardadas, motivo)
                            VALUES (:uid, :sede, :uc, 'Eliminacion')");
                    $stmt->execute([':uid' => $data['usuario_id'], ':sede' => $sede_id, ':uc' => $data['uc']]);
                }
                registrar_log($pdo, 'Eliminar inscripción', 'inscripciones', $insc_id, $justificacion);
                $mensaje = alerta_success('Asignatura eliminada. Crédito resguardado si aplica.');
            }
        } else {
            $mensaje = alerta_error('Debe escribir una justificación.');
        }
    }
}

// Aspirantes en revisión (de la sede)
$aspirantes = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM aspirante_documentos ad WHERE ad.usuario_id = u.id) as docs_subidos,
                              (SELECT COUNT(*) FROM aspirante_documentos ad WHERE ad.usuario_id = u.id AND ad.verificado = true) as docs_verificados
                              FROM usuarios u WHERE u.rol_id = 5 AND u.sede_id = :sede
                              ORDER BY u.fecha_registro DESC");
$aspirantes->execute([':sede' => $sede_id]);
$aspirantes = $aspirantes->fetchAll();

// Estudiantes con inscripciones "Por Cancelar"
$por_cancelar = $pdo->prepare("SELECT DISTINCT u.id, u.tipo_cedula, u.numero_documento, u.nombres, u.apellidos,
                                (SELECT COUNT(*) FROM inscripciones i WHERE i.usuario_id = u.id AND i.estatus = 'Por Cancelar') as materias_pendientes,
                                (SELECT SUM(asig.uc) FROM inscripciones i JOIN secciones sec ON sec.id = i.seccion_id JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo WHERE i.usuario_id = u.id AND i.estatus = 'Por Cancelar') as uc_pendientes
                                FROM usuarios u
                                JOIN inscripciones i ON i.usuario_id = u.id
                                JOIN secciones sec ON sec.id = i.seccion_id
                                WHERE sec.sede_id = :sede AND i.estatus = 'Por Cancelar'
                                ORDER BY u.apellidos");
$por_cancelar->execute([':sede' => $sede_id]);
$por_cancelar = $por_cancelar->fetchAll();

// Créditos resguardados activos en esta sede
$creditos = $pdo->prepare("SELECT cr.*, u.nombres, u.apellidos, u.tipo_cedula, u.numero_documento
                            FROM creditos_resguardados cr
                            JOIN usuarios u ON u.id = cr.usuario_id
                            WHERE cr.sede_origen_id = :sede AND cr.estatus = 'Activo'
                            ORDER BY cr.created_at DESC");
$creditos->execute([':sede' => $sede_id]);
$creditos = $creditos->fetchAll();

$titulo = 'Panel Secretaría';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">UNEFA</span>
            </div>
            <p>Secretaría: <?php echo h($nombre_secre); ?></p>
            <p><small><?php echo h($sede_info['nombre'] ?? 'Sin sede'); ?></small></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Inicio</span></a>
            <a href="#modulo-admisiones" data-modulo="admisiones"><span>📁 Admisiones</span></a>
            <a href="#modulo-taquilla" data-modulo="taquilla"><span>💰 Taquilla Virtual</span></a>
            <a href="#modulo-estudiantes" data-modulo="estudiantes"><span>👥 Maestro Estudiantes</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Panel de Secretaría - <?php echo h($sede_info['nombre'] ?? 'Sin sede'); ?></h2>
            <p><?php echo fecha_hoy_formateada(); ?></p>
        </header>

        <?php echo $mensaje; ?>

        <!-- INICIO -->
        <section id="modulo-inicio" class="module-section">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                <div class="form-section"><h3>📁 <?php echo count($aspirantes); ?></h3><p>Aspirantes en revisión</p></div>
                <div class="form-section"><h3>💰 <?php echo count($por_cancelar); ?></h3><p>Estudiantes por cancelar</p></div>
                <div class="form-section"><h3>🔄 <?php echo count($creditos); ?></h3><p>Créditos resguardados</p></div>
            </div>
        </section>

        <!-- ADMISIONES -->
        <section id="modulo-admisiones" class="module-section" style="display:none;">
            <h3>📁 Consola de Auditoría de Admisiones</h3>
            <?php if (count($aspirantes) === 0): ?>
                <p class="text-muted">No hay aspirantes pendientes de revisión.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Documento</th><th>Nombres</th><th>Email</th><th>Docs</th><th>Estado</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($aspirantes as $asp): ?>
                    <tr>
                        <td><?php echo h($asp['tipo_cedula'] . '-' . $asp['numero_documento']); ?></td>
                        <td><?php echo h($asp['nombres'] . ' ' . $asp['apellidos']); ?></td>
                        <td><?php echo h($asp['email']); ?></td>
                        <td>
                            <?php echo $asp['docs_verificados']; ?>/<?php echo $asp['docs_subidos']; ?>
                            <a href="#" onclick="verDocs(<?php echo $asp['id']; ?>);return false;" style="font-size:0.75rem;margin-left:5px;">📄</a>
                        </td>
                        <td><span class="badge badge-planar"><?php echo h($asp['estado_aspirante'] ?? 'En Revision Digital'); ?></span></td>
                        <td>
                            <?php if (($asp['estado_aspirante'] ?? '') === 'En Revision Digital'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="usuario_id" value="<?php echo $asp['id']; ?>">
                                <button type="submit" name="admitir_aspirante" class="btn-action btn-green"
                                        onclick="return confirm('¿Admitir a <?php echo h($asp['nombres'] . ' ' . $asp['apellidos']); ?> como Estudiante Regular?')">
                                    ✅ Dar Visto Bueno y Admitir
                                </button>
                            </form>
                            <?php else: ?>
                                <span class="badge badge-inscripcion">Admitido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>

        <!-- TAQUILLA VIRTUAL -->
        <section id="modulo-taquilla" class="module-section" style="display:none;">
            <h3>💰 Taquilla Virtual - Conciliación de Pagos</h3>

            <?php if (count($por_cancelar) === 0): ?>
                <p class="text-muted">No hay estudiantes con inscripciones pendientes de pago.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Documento</th><th>Estudiante</th><th>Materias</th><th>UC</th><th>Créditos</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($por_cancelar as $est): ?>
                    <?php
                        // Verificar créditos resguardados
                        $cred = $pdo->prepare("SELECT SUM(uc_resguardadas) as total FROM creditos_resguardados WHERE usuario_id = :uid AND estatus = 'Activo'");
                        $cred->execute([':uid' => $est['id']]);
                        $cred_data = $cred->fetch();
                        $uc_resguardadas = (int)($cred_data['total'] ?? 0);
                        $uc_a_pagar = max(0, $est['uc_pendientes'] - $uc_resguardadas);
                    ?>
                    <tr>
                        <td><?php echo h($est['tipo_cedula'] . '-' . $est['numero_documento']); ?></td>
                        <td><strong><?php echo h($est['nombres'] . ' ' . $est['apellidos']); ?></strong></td>
                        <td><?php echo $est['materias_pendientes']; ?></td>
                        <td><?php echo $est['uc_pendientes']; ?> UC</td>
                        <td>
                            <?php if ($uc_resguardadas > 0): ?>
                                <span class="badge badge-planar" style="background:#d4a017;">⚠️ <?php echo $uc_resguardadas; ?> UC resguardadas</span>
                                <br><small>Total a pagar: <?php echo $uc_a_pagar; ?> UC</small>
                            <?php else: ?>
                                <span style="color:#999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline-block;border:1px solid #ddd;padding:10px;border-radius:8px;min-width:300px;">
                                <input type="hidden" name="usuario_id" value="<?php echo $est['id']; ?>">
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;font-size:0.75rem;">
                                    <select name="banco" required style="padding:5px;">
                                        <option value="">Banco</option>
                                        <option>Banco de Venezuela</option>
                                        <option>Banesco</option>
                                        <option>Mercantil</option>
                                        <option>Provincial</option>
                                        <option>Bicentenario</option>
                                    </select>
                                    <input type="text" name="referencia" placeholder="Referencia" required style="padding:5px;">
                                    <input type="number" name="monto" placeholder="Monto" step="0.01" required style="padding:5px;">
                                    <input type="date" name="fecha_pago" required style="padding:5px;">
                                </div>
                                <button type="submit" name="validar_pago" class="btn-action btn-green" style="margin-top:5px;width:100%;">✅ Validar Pago y Formalizar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <h3 style="margin-top:25px;">🔄 Módulo de Ajuste y Corrección de Carga</h3>
            <div class="form-section">
                <form method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta inscripción?')">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Buscar inscripción por ID</label>
                            <input type="number" name="inscripcion_id" placeholder="ID de inscripción" required>
                        </div>
                        <div class="form-group">
                            <label>Justificación <span class="req">*</span></label>
                            <textarea name="justificacion" placeholder="Motivo de la eliminación" required rows="2"></textarea>
                        </div>
                    </div>
                    <button type="submit" name="eliminar_inscripcion" class="btn-action btn-orange">❌ Eliminar Asignatura</button>
                </form>
            </div>
        </section>

        <!-- MAESTRO ESTUDIANTES -->
        <section id="modulo-estudiantes" class="module-section" style="display:none;">
            <h3>👥 Maestro de Estudiantes Regulares</h3>
            <?php
            $estudiantes = $pdo->prepare("SELECT u.*,
                                          (SELECT COUNT(*) FROM inscripciones i WHERE i.usuario_id = u.id AND i.estatus = 'Formalizada') as materias_activas,
                                          (SELECT ROUND(AVG(an.nota),1) FROM actas_notas an WHERE an.usuario_id = u.id AND an.estatus = 'Definitiva') as promedio
                                          FROM usuarios u WHERE u.rol_id = 6 AND u.sede_id = :sede
                                          ORDER BY u.apellidos LIMIT 50");
            $estudiantes->execute([':sede' => $sede_id]);
            ?>
            <table class="data-table">
                <thead>
                    <tr><th>Documento</th><th>Nombres</th><th>Email</th><th>Materias</th><th>Promedio</th></tr>
                </thead>
                <tbody>
                    <?php while ($est = $estudiantes->fetch()): ?>
                    <tr>
                        <td><?php echo h($est['tipo_cedula'] . '-' . $est['numero_documento']); ?></td>
                        <td><?php echo h($est['nombres'] . ' ' . $est['apellidos']); ?></td>
                        <td><?php echo h($est['email']); ?></td>
                        <td><?php echo $est['materias_activas']; ?></td>
                        <td><?php echo $est['promedio'] ?? '—'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<script>
function verDocs(usuarioId) {
    const popup = window.open('', 'Documentos', 'width=600,height=500,scrollbars=yes');
    if (!popup) { alert('Permite ventanas emergentes para ver documentos.'); return; }
    popup.document.write('<html><head><title>Documentos del Aspirante</title>');
    popup.document.write('<style>body{font-family:sans-serif;padding:20px;}');
    popup.document.write('a{display:block;padding:8px;margin:4px 0;background:#f0f0f0;border-radius:4px;text-decoration:none;color:#2c3e50;}');
    popup.document.write('a:hover{background:#d5e8f5;}</style></head><body>');
    popup.document.write('<h2>📁 Documentos del Aspirante #' + usuarioId + '</h2>');
    popup.document.write('<div id="docs-list">Cargando...</div>');
    popup.document.write('<script>
        fetch("../../controlador/api/listar_documentos.php?uid=" + ' + usuarioId + ')
            .then(r => r.json())
            .then(docs => {
                const list = document.getElementById("docs-list");
                if (!docs || docs.length === 0 || docs.error) {
                    list.innerHTML = "<p>Este aspirante no ha subido documentos a\u00fan.</p>";
                    return;
                }
                let html = "";
                docs.forEach(d => {
                    const estado = d.verificado ? "\u2705 Verificado" : "\u2B1C Pendiente";
                    html += "<div style=\'margin:8px 0;padding:10px;background:#f9f9f9;border-radius:6px;\'>";
                    html += "<strong>" + d.tipo + "</strong> - " + estado;
                    html += " <a href=\'../../controlador/descargar_documento.php?id=" + d.id + "\' target=\'_blank\' style=\'display:inline;padding:4px 8px;\'>\uD83D\uDCC4 Ver</a>";
                    if (d.observaciones) html += "<div style=\'font-size:0.75rem;color:#e67e22;margin-top:4px;\'>" + d.observaciones + "</div>";
                    html += "</div>";
                });
                list.innerHTML = html;
            }).catch(() => {
                document.getElementById("docs-list").innerHTML = "<p>Error al cargar documentos.</p>";
            });
    <\/script>');
    popup.document.write('</body></html>');
    popup.document.close();
}
</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
