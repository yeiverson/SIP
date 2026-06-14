<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$nombre_admin = $_SESSION['nombre_full'];
$mensaje = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear sede
    if (isset($_POST['crear_sede'])) {
        $nombre = trim($_POST['sede_nombre']);
        $ubicacion = trim($_POST['sede_ubicacion']);
        $codigo = strtoupper(trim($_POST['sede_codigo']));
        if ($nombre && $codigo) {
            try {
                $stmt = $pdo->prepare('INSERT INTO sedes (nombre, ubicacion, codigo) VALUES (:n, :u, :c)');
                $stmt->execute([':n' => $nombre, ':u' => $ubicacion, ':c' => $codigo]);
                registrar_log($pdo, 'Crear sede', 'sedes', $pdo->lastInsertId(), "Sede: $nombre ($codigo)");
                $mensaje = alerta_success("Sede '$nombre' creada exitosamente.");
            } catch (PDOException $e) {
                $mensaje = $e->getCode() == '23505' ? alerta_error("El código '$codigo' ya existe.") : alerta_error("Error al crear sede.");
            }
        }
    }
    // Asignar director a sede
    elseif (isset($_POST['asignar_director'])) {
        $sede_id = (int)$_POST['sede_id'];
        $director_id = (int)$_POST['director_id'];
        if ($sede_id && $director_id) {
            $stmt = $pdo->prepare('UPDATE usuarios SET sede_id = :sede WHERE id = :uid AND rol_id = 7');
            $stmt->execute([':sede' => $sede_id, ':uid' => $director_id]);
            registrar_log($pdo, 'Asignar director a sede', 'usuarios', $director_id, "Director ID $director_id -> Sede ID $sede_id");
            $mensaje = alerta_success('Director asignado a la sede.');
        }
    }
    // Aprobar solicitud docente
    elseif (isset($_POST['aprobar_solicitud'])) {
        $sol_id = (int)$_POST['solicitud_id'];
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT * FROM solicitudes_docentes WHERE id = :id AND estatus='Pendiente'");
            $stmt->execute([':id' => $sol_id]);
            $sol = $stmt->fetch();
            if ($sol) {
                // Crear usuario docente
                $pass_hash = password_hash(bin2hex(random_bytes(4)), PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (tipo_cedula, numero_documento, nombres, apellidos, email, password, rol_id, sede_id, estatus)
                        VALUES (:tipo, :doc, :nom, :ape, :email, :pass, 3, :sede, 'Activo')");
                $stmt->execute([
                    ':tipo' => $sol['tipo_documento'],
                    ':doc'  => $sol['numero_documento'],
                    ':nom'  => $sol['nombres'],
                    ':ape'  => $sol['apellidos'],
                    ':email'=> $sol['email'],
                    ':pass' => $pass_hash,
                    ':sede' => $sol['sede_id'],
                ]);
                // Actualizar solicitud
                $stmt = $pdo->prepare("UPDATE solicitudes_docentes SET estatus='Aprobado', admin_id=:admin, resuelto_at=NOW() WHERE id=:id");
                $stmt->execute([':admin' => $_SESSION['usuario_id'], ':id' => $sol_id]);
                $pdo->commit();
                registrar_log($pdo, 'Aprobar solicitud docente', 'solicitudes_docentes', $sol_id, "Docente {$sol['nombres']} {$sol['apellidos']}");
                $mensaje = alerta_success("Docente {$sol['nombres']} {$sol['apellidos']} creado exitosamente.");
            } else {
                $pdo->rollBack();
                $mensaje = alerta_error('Solicitud no encontrada o ya procesada.');
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = alerta_error('Error al procesar: ' . $e->getMessage());
        }
    }
    // Habilitar edición temporal de acta
    elseif (isset($_POST['habilitar_acta'])) {
        $acta_id = (int)$_POST['acta_id'];
        $stmt = $pdo->prepare("UPDATE actas_notas SET estatus='Borrador', updated_at=NOW() WHERE id=:id AND estatus='Definitiva'");
        $stmt->execute([':id' => $acta_id]);
        if ($stmt->rowCount() > 0) {
            registrar_log($pdo, 'Reapertura de acta', 'actas_notas', $acta_id, "Acta ID $acta_id reabierta por 24h");
            $mensaje = alerta_success('Acta reabierta para edición temporal (24h).');
        } else {
            $mensaje = alerta_error('Acta no encontrada o ya está en borrador.');
        }
    }
    // Reversión técnica de saldo
    elseif (isset($_POST['reversar_saldo'])) {
        $credito_id = (int)$_POST['credito_id'];
        $stmt = $pdo->prepare("UPDATE creditos_resguardados SET estatus='Reversado' WHERE id=:id AND estatus='Activo'");
        $stmt->execute([':id' => $credito_id]);
        if ($stmt->rowCount() > 0) {
            registrar_log($pdo, 'Reversión de crédito', 'creditos_resguardados', $credito_id, "Reversión técnica de saldo");
            $mensaje = alerta_success('Saldo revertido exitosamente.');
        } else {
            $mensaje = alerta_error('Crédito no encontrado o ya fue procesado.');
        }
    }
    // Crear pregunta del baremo
    elseif (isset($_POST['crear_pregunta'])) {
        $pregunta = trim($_POST['pregunta']);
        $categoria = trim($_POST['categoria']);
        $orden = (int)$_POST['orden'];
        if ($pregunta && $categoria) {
            $stmt = $pdo->prepare("INSERT INTO baremo_preguntas (pregunta, categoria, orden) VALUES (:p, :c, :o)");
            $stmt->execute([':p' => $pregunta, ':c' => $categoria, ':o' => $orden]);
            registrar_log($pdo, 'Crear pregunta', 'baremo_preguntas', $pdo->lastInsertId(), "Nueva pregunta: " . substr($pregunta, 0, 60));
            $mensaje = alerta_success('Pregunta creada.');
        } else {
            $mensaje = alerta_error('Completa todos los campos.');
        }
    }
    // Editar pregunta del baremo
    elseif (isset($_POST['editar_pregunta'])) {
        $id = (int)$_POST['id_pregunta'];
        $pregunta = trim($_POST['pregunta']);
        $categoria = trim($_POST['categoria']);
        $orden = (int)$_POST['orden'];
        if ($id && $pregunta && $categoria) {
            $stmt = $pdo->prepare("UPDATE baremo_preguntas SET pregunta=:p, categoria=:c, orden=:o WHERE id=:id");
            $stmt->execute([':p' => $pregunta, ':c' => $categoria, ':o' => $orden, ':id' => $id]);
            registrar_log($pdo, 'Editar pregunta', 'baremo_preguntas', $id, "Pregunta editada: " . substr($pregunta, 0, 60));
            $mensaje = alerta_success('Pregunta actualizada.');
        } else {
            $mensaje = alerta_error('Completa todos los campos.');
        }
    }
    // Eliminar pregunta del baremo
    elseif (isset($_POST['eliminar_pregunta'])) {
        $id = (int)$_POST['id_pregunta'];
        $stmt = $pdo->prepare("DELETE FROM respuestas_baremo WHERE id_pregunta=:id");
        $stmt->execute([':id' => $id]);
        $stmt = $pdo->prepare("DELETE FROM baremo_preguntas WHERE id=:id");
        $stmt->execute([':id' => $id]);
        registrar_log($pdo, 'Eliminar pregunta', 'baremo_preguntas', $id, 'Pregunta eliminada del baremo');
        $mensaje = alerta_success('Pregunta eliminada.');
    }
}

// --- CONSULTAS PARA LAS VISTAS ---

// Sedes
$sedes = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM usuarios u WHERE u.sede_id = s.id AND u.rol_id = 7) as directores
                       FROM sedes s ORDER BY s.nombre")->fetchAll();

// Directores sin sede
$directores_disponibles = $pdo->query("SELECT id, nombres, apellidos, email FROM usuarios WHERE rol_id = 7 AND (sede_id IS NULL OR sede_id = 0)")->fetchAll();

// Solicitudes pendientes
$solicitudes = $pdo->query("SELECT sd.*, s.nombre as sede_nombre
                             FROM solicitudes_docentes sd
                             JOIN sedes s ON s.id = sd.sede_id
                             WHERE sd.estatus = 'Pendiente'
                             ORDER BY sd.created_at DESC")->fetchAll();

// Logs recientes
$logs = $pdo->query("SELECT l.*, u.nombres, u.apellidos
                      FROM logs_auditoria l
                      LEFT JOIN usuarios u ON u.id = l.usuario_id
                      ORDER BY l.created_at DESC LIMIT 50")->fetchAll();

// Créditos resguardados activos
$creditos = $pdo->query("SELECT cr.*, u.nombres, u.apellidos, u.tipo_cedula, u.numero_documento
                          FROM creditos_resguardados cr
                          JOIN usuarios u ON u.id = cr.usuario_id
                          WHERE cr.estatus = 'Activo'
                          ORDER BY cr.created_at DESC")->fetchAll();

// Actas definitivas (para posible reapertura)
$actas_cerradas = $pdo->query("SELECT an.*, u.nombres, u.apellidos, asig.nombre as materia, sec.seccion
                                FROM actas_notas an
                                JOIN usuarios u ON u.id = an.usuario_id
                                JOIN secciones sec ON sec.id = an.seccion_id
                                JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                                WHERE an.estatus = 'Definitiva'
                                ORDER BY an.updated_at DESC LIMIT 20")->fetchAll();

// Preguntas del baremo
$baremo_preguntas = $pdo->query("SELECT * FROM baremo_preguntas ORDER BY categoria, orden")->fetchAll();

$titulo = 'Panel de Administración';
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
            <p>Admin: <?php echo h($nombre_admin); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Dashboard</span></a>
            <a href="#modulo-sedes" data-modulo="sedes"><span>🌐 Sedes/Núcleos</span></a>
            <a href="#modulo-solicitudes" data-modulo="solicitudes"><span>📨 Solicitudes Docentes</span></a>
            <a href="#modulo-llaves" data-modulo="llaves"><span>🔑 Llaves Digitales</span></a>
            <a href="#modulo-auditoria" data-modulo="auditoria"><span>📊 Auditoría</span></a>
            <a href="#modulo-creditos" data-modulo="creditos"><span>💰 Créditos Resguardados</span></a>
            <a href="#modulo-baremo" data-modulo="baremo"><span>📋 Baremo</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h2>Panel de Control del Administrador</h2>
            <p><?php echo fecha_hoy_formateada(); ?> &middot; Rol: Administrador del Sistema</p>
        </header>

        <?php echo $mensaje; ?>

        <!-- ===== MÓDULO A: SEDES ===== -->
        <section id="modulo-sedes" class="module-section">
            <div class="form-section">
                <h3>🌐 Gestión de Sedes / Núcleos</h3>
                <form method="POST" class="form-inline">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label>Nombre de la Sede</label>
                            <input type="text" name="sede_nombre" placeholder="Ej: La Isabelica" required>
                        </div>
                        <div class="form-group">
                            <label>Ubicación</label>
                            <input type="text" name="sede_ubicacion" placeholder="Ej: Estado Carabobo">
                        </div>
                        <div class="form-group">
                            <label>Código</label>
                            <input type="text" name="sede_codigo" placeholder="Ej: LIS" required maxlength="10">
                        </div>
                    </div>
                    <button type="submit" name="crear_sede" class="btn-submit">Crear Sede / Núcleo</button>
                </form>
            </div>

            <div class="form-section" style="margin-top:20px;">
                <h4>Asignar Director a Sede</h4>
                <form method="POST" class="form-inline">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Director</label>
                            <select name="director_id" required>
                                <option value="">Seleccione un director</option>
                                <?php foreach ($directores_disponibles as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo h($d['nombres'] . ' ' . $d['apellidos'] . ' (' . $d['email'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Sede</label>
                            <select name="sede_id" required>
                                <option value="">Seleccione sede</option>
                                <?php foreach ($sedes as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo h($s['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="asignar_director" class="btn-submit">Asignar Director</button>
                </form>
            </div>

            <div class="table-section" style="margin-top:20px;">
                <h4>Sedes Registradas</h4>
                <table class="data-table">
                    <thead>
                        <tr><th>Código</th><th>Nombre</th><th>Ubicación</th><th>Fase</th><th>Directores</th><th>Activa</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sedes as $s): ?>
                        <tr>
                            <td><strong><?php echo h($s['codigo']); ?></strong></td>
                            <td><?php echo h($s['nombre']); ?></td>
                            <td><?php echo h($s['ubicacion']); ?></td>
                            <td><span class="badge <?php echo $s['fase_actual']==1?'badge-planar':'badge-inscripcion'; ?>">Fase <?php echo $s['fase_actual']; ?></span></td>
                            <td><?php echo $s['directores']; ?></td>
                            <td><?php echo $s['activa'] ? '✅' : '❌'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== MÓDULO B: SOLICITUDES ===== -->
        <section id="modulo-solicitudes" class="module-section" style="display:none;">
            <h3>📨 Bandeja de Solicitudes Urgentes de Docentes</h3>
            <?php if (count($solicitudes) === 0): ?>
                <p class="text-muted">No hay solicitudes pendientes.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Fecha</th><th>Sede</th><th>Identidad</th><th>Nombres</th><th>Email</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $sol): ?>
                    <tr>
                        <td><?php echo formatear_fecha($sol['created_at']); ?></td>
                        <td><?php echo h($sol['sede_nombre']); ?></td>
                        <td><?php echo h($sol['tipo_documento'] . '-' . $sol['numero_documento']); ?></td>
                        <td><?php echo h($sol['nombres'] . ' ' . $sol['apellidos']); ?></td>
                        <td><?php echo h($sol['email']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="solicitud_id" value="<?php echo $sol['id']; ?>">
                                <button type="submit" name="aprobar_solicitud" class="btn-action btn-green">✅ Aprobar y Crear Cuenta</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>

        <!-- ===== MÓDULO C: LLAVES DIGITALES ===== -->
        <section id="modulo-llaves" class="module-section" style="display:none;">
            <h3>🔑 Consola de Permisos Especiales (Llaves Digitales)</h3>

            <div class="form-section">
                <h4>Reapertura de Actas de Notas</h4>
                <p style="margin-bottom:10px;color:#666;">Actas en estado Definitivo que pueden ser reabiertas por 24h:</p>
                <?php if (count($actas_cerradas) === 0): ?>
                    <p class="text-muted">No hay actas cerradas recientemente.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr><th>Estudiante</th><th>Materia</th><th>Sección</th><th>Nota</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actas_cerradas as $acta): ?>
                        <tr>
                            <td><?php echo h($acta['nombres'] . ' ' . $acta['apellidos']); ?></td>
                            <td><?php echo h($acta['materia']); ?></td>
                            <td><?php echo h($acta['seccion']); ?></td>
                            <td><?php echo $acta['nota'] ?? ($acta['inasistencia'] ? 'N/S' : '—'); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="acta_id" value="<?php echo $acta['id']; ?>">
                                    <button type="submit" name="habilitar_acta" class="btn-action btn-orange">🔓 Habilitar Edición (24h)</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== MÓDULO D: AUDITORÍA ===== -->
        <section id="modulo-auditoria" class="module-section" style="display:none;">
            <h3>📊 Consola de Auditoría (Logs Inmutables)</h3>
            <div class="table-section">
                <table class="data-table">
                    <thead>
                        <tr><th>Fecha/Hora</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>IP</th><th>Detalle</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo formatear_fecha($log['created_at']) . ' ' . date('H:i', strtotime($log['created_at'])); ?></td>
                            <td><?php echo h(($log['nombres'] ?? 'Sistema') . ' ' . ($log['apellidos'] ?? '')); ?></td>
                            <td><?php echo h($log['accion']); ?></td>
                            <td><?php echo h($log['entidad'] ?? '—'); ?> #<?php echo $log['entidad_id'] ?? '—'; ?></td>
                            <td><?php echo h($log['direccion_ip']); ?></td>
                            <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;"><?php echo h(substr($log['detalle'] ?? '', 0, 100)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== MÓDULO E: CRÉDITOS RESGUARDADOS ===== -->
        <section id="modulo-creditos" class="module-section" style="display:none;">
            <h3>💰 Control de Créditos Resguardados</h3>
            <?php if (count($creditos) === 0): ?>
                <p class="text-muted">No hay créditos resguardados activos.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Estudiante</th><th>Documento</th><th>UC</th><th>Monto</th><th>Motivo</th><th>Fecha</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($creditos as $c): ?>
                    <tr>
                        <td><?php echo h($c['nombres'] . ' ' . $c['apellidos']); ?></td>
                        <td><?php echo h($c['tipo_cedula'] . '-' . $c['numero_documento']); ?></td>
                        <td><?php echo $c['uc_resguardadas']; ?> UC</td>
                        <td><?php echo $c['monto_resguardado'] ? 'Bs. ' . number_format($c['monto_resguardado'], 2) : '—'; ?></td>
                        <td><?php echo h($c['motivo'] ?? '—'); ?></td>
                        <td><?php echo formatear_fecha($c['created_at']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="credito_id" value="<?php echo $c['id']; ?>">
                                <button type="submit" name="reversar_saldo" class="btn-action btn-orange">🔄 Reversión Técnica</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>

        <!-- ===== MÓDULO F: BAREMO ===== -->
        <section id="modulo-baremo" class="module-section" style="display:none;">
            <h3>📋 Gestión del Baremo</h3>
            <p class="text-muted">Administra las preguntas del baremo de admisión para aspirantes.</p>

            <!-- Formulario nueva pregunta -->
            <div class="form-section">
                <h4>➕ Nueva Pregunta</h4>
                <form method="POST" class="form-grid-3">
                    <div>
                        <label>Categoría</label>
                        <select name="categoria" required>
                            <option value="Academico">Académico</option>
                            <option value="Investigacion">Investigación</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div>
                        <label>Orden</label>
                        <input type="number" name="orden" min="1" max="99" value="1" required>
                    </div>
                    <div style="grid-column: span 3;">
                        <label>Pregunta</label>
                        <input type="text" name="pregunta" required placeholder="Texto de la pregunta...">
                    </div>
                    <div>
                        <button type="submit" name="crear_pregunta" class="btn-submit">➕ Crear Pregunta</button>
                    </div>
                </form>
            </div>

            <!-- Listado de preguntas -->
            <div class="table-section">
                <h4>📋 Preguntas Existentes</h4>
                <?php if (count($baremo_preguntas) === 0): ?>
                    <p class="text-muted">No hay preguntas registradas.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Categoría</th><th>Orden</th><th>Pregunta</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baremo_preguntas as $bp): ?>
                        <tr>
                            <td><?php echo $bp['id']; ?></td>
                            <td><span class="badge badge-<?php echo strtolower($bp['categoria']); ?>"><?php echo h($bp['categoria']); ?></span></td>
                            <td><?php echo $bp['orden']; ?></td>
                            <td><?php echo h($bp['pregunta']); ?></td>
                            <td>
                                <button class="btn-action btn-blue" onclick="editarPregunta(<?php echo $bp['id']; ?>)">✏️ Editar</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta pregunta? También se borrarán las respuestas asociadas.');">
                                    <input type="hidden" name="id_pregunta" value="<?php echo $bp['id']; ?>">
                                    <button type="submit" name="eliminar_pregunta" class="btn-action btn-red">🗑️ Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Modal editar pregunta -->
            <div id="modal-editar-pregunta" class="modal-overlay" style="display:none;">
                <div class="modal-box">
                    <h4>✏️ Editar Pregunta</h4>
                    <form method="POST" class="form-grid-3">
                        <input type="hidden" name="id_pregunta" id="edit-id">
                        <div>
                            <label>Categoría</label>
                            <select name="categoria" id="edit-categoria" required>
                                <option value="Academico">Académico</option>
                                <option value="Investigacion">Investigación</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div>
                            <label>Orden</label>
                            <input type="number" name="orden" id="edit-orden" min="1" max="99" required>
                        </div>
                        <div style="grid-column: span 3;">
                            <label>Pregunta</label>
                            <input type="text" name="pregunta" id="edit-pregunta" required>
                        </div>
                        <div style="grid-column: span 3; display: flex; gap: 8px;">
                            <button type="submit" name="editar_pregunta" class="btn-submit">💾 Guardar Cambios</button>
                            <button type="button" class="btn-submit btn-cancel" onclick="cerrarModalPregunta()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            const preguntas = <?php echo json_encode($baremo_preguntas); ?>;
            function editarPregunta(id) {
                const p = preguntas.find(x => x.id === id);
                if (!p) return;
                document.getElementById('edit-id').value = p.id;
                document.getElementById('edit-categoria').value = p.categoria;
                document.getElementById('edit-orden').value = p.orden;
                document.getElementById('edit-pregunta').value = p.pregunta;
                document.getElementById('modal-editar-pregunta').style.display = 'flex';
            }
            function cerrarModalPregunta() {
                document.getElementById('modal-editar-pregunta').style.display = 'none';
            }
            </script>
        </section>
    </main>
</div>



<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
