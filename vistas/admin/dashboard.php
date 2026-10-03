<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../modelos/Usuario.php';
require_once __DIR__ . '/../../modelos/Seccion.php';
require_once __DIR__ . '/../../modelos/Baremo.php';

iniciar_sesion_segura();

$nombre_admin = $_SESSION['nombre_full'];
$mensaje = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $mensaje = alerta_error('Token de seguridad inválido. Por favor recargue el formulario.');
    } else {
        // 1. Crear nuevo usuario directamente (Admin)
        if (isset($_POST['crear_usuario'])) {
            $tipo_doc = strtoupper(trim($_POST['tipo_documento'] ?? 'V'));
            $num_doc  = trim($_POST['numero_documento'] ?? '');
            $nombres  = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $pass     = trim($_POST['password'] ?? '');
            $rol_id   = (int)($_POST['rol_id'] ?? 5);
            $sede_id  = !empty($_POST['sede_id']) ? (int)$_POST['sede_id'] : null;

            if ($tipo_doc && $num_doc && $nombres && $apellidos && $email && $pass) {
                try {
                    $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (tipo_cedula, numero_documento, cedula, nombres, apellidos, email, password, rol_id, sede_id, estatus, estado_aspirante)
                                           VALUES (:tipo, :ndoc, :ci, :nom, :ape, :mail, :pass, :rol, :sede, 'Activo', 'Admitido')
                                           RETURNING id");
                    $stmt->execute([
                        ':tipo' => $tipo_doc,
                        ':ndoc' => $num_doc,
                        ':ci'   => solo_numeros($num_doc) ?: '0',
                        ':nom'  => $nombres,
                        ':ape'  => $apellidos,
                        ':mail' => $email,
                        ':pass' => $pass_hash,
                        ':rol'  => $rol_id,
                        ':sede' => $sede_id,
                    ]);
                    $nuevo_id = $stmt->fetchColumn();
                    registrar_log($pdo, 'Crear usuario', 'usuarios', $nuevo_id, "Usuario $nombres $apellidos creado con rol ID $rol_id");
                    $mensaje = alerta_success("Usuario '$nombres $apellidos' creado exitosamente con credenciales de acceso.");
                } catch (PDOException $e) {
                    $mensaje = ($e->getCode() === '23505')
                        ? alerta_error("El correo o documento ya se encuentra registrado.")
                        : alerta_error("Error al registrar usuario: " . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error("Complete todos los campos obligatorios para registrar al usuario.");
            }
        }
        // 2. Cambiar estatus de usuario (Activo / Bloqueado)
        elseif (isset($_POST['cambiar_estatus_usuario'])) {
            $uid = (int)$_POST['usuario_id'];
            $nuevo_estatus = ($_POST['estatus'] === 'Activo') ? 'Bloqueado' : 'Activo';
            if ($uid !== (int)$_SESSION['usuario_id']) {
                $stmt = $pdo->prepare("UPDATE usuarios SET estatus = :est WHERE id = :id");
                $stmt->execute([':est' => $nuevo_estatus, ':id' => $uid]);
                registrar_log($pdo, 'Cambiar estatus usuario', 'usuarios', $uid, "Estatus cambiado a $nuevo_estatus");
                $mensaje = alerta_success("Estatus del usuario actualizado a $nuevo_estatus.");
            } else {
                $mensaje = alerta_error("No puedes bloquear tu propia cuenta de administrador.");
            }
        }
        // 3. Crear sede
        elseif (isset($_POST['crear_sede'])) {
            $nombre = trim($_POST['sede_nombre']);
            $ubicacion = trim($_POST['sede_ubicacion']);
            $codigo = strtoupper(trim($_POST['sede_codigo']));
            if ($nombre && $codigo) {
                try {
                    $stmt = $pdo->prepare('INSERT INTO sedes (nombre, ubicacion, codigo, fase_actual) VALUES (:n, :u, :c, 1) RETURNING id');
                    $stmt->execute([':n' => $nombre, ':u' => $ubicacion, ':c' => $codigo]);
                    $sede_id = $stmt->fetchColumn();
                    registrar_log($pdo, 'Crear sede', 'sedes', $sede_id, "Sede: $nombre ($codigo)");
                    $mensaje = alerta_success("Sede '$nombre' creada exitosamente.");
                } catch (PDOException $e) {
                    $mensaje = $e->getCode() == '23505' ? alerta_error("El código '$codigo' ya existe.") : alerta_error("Error al crear sede.");
                }
            }
        }
        // 4. Asignar director a sede
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
        // 5. Aprobar solicitud docente
        elseif (isset($_POST['aprobar_solicitud'])) {
            $sol_id = (int)$_POST['solicitud_id'];
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT * FROM solicitudes_docentes WHERE id = :id AND estatus='Pendiente'");
                $stmt->execute([':id' => $sol_id]);
                $sol = $stmt->fetch();
                if ($sol) {
                    $pass_temp = bin2hex(random_bytes(4));
                    $pass_hash = password_hash($pass_temp, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (tipo_cedula, numero_documento, cedula, nombres, apellidos, email, password, rol_id, sede_id, estatus, estado_aspirante)
                            VALUES (:tipo, :doc, :ci, :nom, :ape, :email, :pass, 3, :sede, 'Activo', 'Admitido')
                            RETURNING id");
                    $stmt->execute([
                        ':tipo' => $sol['tipo_documento'],
                        ':doc'  => $sol['numero_documento'],
                        ':ci'   => solo_numeros($sol['numero_documento']) ?: '0',
                        ':nom'  => $sol['nombres'],
                        ':ape'  => $sol['apellidos'],
                        ':email'=> $sol['email'],
                        ':pass' => $pass_hash,
                        ':sede' => $sol['sede_id'],
                    ]);
                    $new_docente_id = $stmt->fetchColumn();

                    $stmt = $pdo->prepare("UPDATE solicitudes_docentes SET estatus='Aprobado', admin_id=:admin, resuelto_at=NOW() WHERE id=:id");
                    $stmt->execute([':admin' => $_SESSION['usuario_id'], ':id' => $sol_id]);
                    $pdo->commit();

                    registrar_log($pdo, 'Aprobar solicitud docente', 'solicitudes_docentes', $sol_id, "Docente {$sol['nombres']} {$sol['apellidos']}");
                    $mensaje = alerta_success("Docente {$sol['nombres']} {$sol['apellidos']} activado en sistema. Clave temporal: <strong>$pass_temp</strong>");
                } else {
                    $pdo->rollBack();
                    $mensaje = alerta_error('Solicitud no encontrada o ya procesada.');
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = alerta_error('Error al procesar: ' . $e->getMessage());
            }
        }
        // 6. Habilitar edición temporal de acta
        elseif (isset($_POST['habilitar_acta'])) {
            $acta_id = (int)$_POST['acta_id'];
            $stmt = $pdo->prepare("UPDATE actas_notas SET estatus='Borrador', updated_at=NOW() WHERE id=:id AND estatus='Definitiva'");
            $stmt->execute([':id' => $acta_id]);
            if ($stmt->rowCount() > 0) {
                registrar_log($pdo, 'Reapertura de acta', 'actas_notas', $acta_id, "Acta ID $acta_id reabierta temporalmente");
                $mensaje = alerta_success('Acta reabierta en modo borrador para corrección de notas.');
            } else {
                $mensaje = alerta_error('Acta no encontrada o ya se encuentra en borrador.');
            }
        }
        // 7. Reversión técnica de saldo
        elseif (isset($_POST['reversar_saldo'])) {
            $credito_id = (int)$_POST['credito_id'];
            $stmt = $pdo->prepare("UPDATE creditos_resguardados SET estatus='Reversado', aplicado_at=NOW() WHERE id=:id AND estatus='Activo'");
            $stmt->execute([':id' => $credito_id]);
            if ($stmt->rowCount() > 0) {
                registrar_log($pdo, 'Reversión de crédito', 'creditos_resguardados', $credito_id, "Reversión técnica de saldo");
                $mensaje = alerta_success('Saldo resguardado revertido exitosamente.');
            } else {
                $mensaje = alerta_error('Crédito no encontrado o ya fue procesado.');
            }
        }
        // 8. Baremo Digital: Crear pregunta
        elseif (isset($_POST['crear_pregunta'])) {
            $pregunta  = trim($_POST['pregunta'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $orden     = (int)($_POST['orden'] ?? 1);
            if ($pregunta && $categoria) {
                try {
                    $preg_id = Baremo::crearPregunta($pdo, $pregunta, $categoria, $orden);
                    registrar_log($pdo, 'Crear pregunta baremo', 'baremo_preguntas', $preg_id, "Pregunta añadida: " . substr($pregunta, 0, 60));
                    $mensaje = alerta_success('Pregunta agregada exitosamente al Baremo Digital.');
                } catch (Exception $e) {
                    $mensaje = alerta_error('Error al crear la pregunta: ' . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error('Debe ingresar el texto de la pregunta y la categoría.');
            }
        }
        // 9. Baremo Digital: Editar / Actualizar pregunta
        elseif (isset($_POST['editar_pregunta'])) {
            $preg_id   = (int)($_POST['pregunta_id'] ?? 0);
            $pregunta  = trim($_POST['pregunta'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $orden     = (int)($_POST['orden'] ?? 1);
            if ($preg_id > 0 && $pregunta && $categoria) {
                try {
                    Baremo::actualizarPregunta($pdo, $preg_id, $pregunta, $categoria, $orden);
                    registrar_log($pdo, 'Editar pregunta baremo', 'baremo_preguntas', $preg_id, "Pregunta #$preg_id modificada");
                    $mensaje = alerta_success("Pregunta #$preg_id actualizada correctamente en el Baremo Digital.");
                } catch (Exception $e) {
                    $mensaje = alerta_error('Error al actualizar la pregunta: ' . $e->getMessage());
                }
            } else {
                $mensaje = alerta_error('Datos incompletos para actualizar la pregunta.');
            }
        }
        // 10. Baremo Digital: Eliminar pregunta
        elseif (isset($_POST['eliminar_pregunta'])) {
            $preg_id = (int)($_POST['pregunta_id'] ?? 0);
            if ($preg_id > 0) {
                try {
                    Baremo::eliminarPregunta($pdo, $preg_id);
                    registrar_log($pdo, 'Eliminar pregunta baremo', 'baremo_preguntas', $preg_id, "Pregunta #$preg_id eliminada");
                    $mensaje = alerta_success("Pregunta #$preg_id eliminada del Baremo Digital.");
                } catch (Exception $e) {
                    $mensaje = alerta_error('Error al eliminar la pregunta: ' . $e->getMessage());
                }
            }
        }
    }
}

// --- CONSULTAS Y MÉTRICAS DE GESTIÓN (A través de Modelos) ---
$totalUsuarios = Usuario::contar($pdo);
$totalEstudiantes = Usuario::contar($pdo, 6);
$totalAspirantes = Usuario::contar($pdo, 5);
$totalSedes = $pdo->query("SELECT COUNT(*) FROM sedes WHERE activa = true")->fetchColumn();
$totalSecciones = Seccion::contar($pdo, true);
$totalLogs = $pdo->query("SELECT COUNT(*) FROM logs_auditoria")->fetchColumn();

// Sedes
$sedes = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM usuarios u WHERE u.sede_id = s.id AND u.rol_id = 7) as directores
                       FROM sedes s ORDER BY s.nombre")->fetchAll();

$directores_disponibles = $pdo->query("SELECT id, nombres, apellidos, email FROM usuarios WHERE rol_id = 7 AND (sede_id IS NULL OR sede_id = 0)")->fetchAll();

// Solicitudes docentes
$solicitudes = $pdo->query("SELECT sd.*, s.nombre as sede_nombre
                             FROM solicitudes_docentes sd
                             JOIN sedes s ON s.id = sd.sede_id
                             WHERE sd.estatus = 'Pendiente'
                             ORDER BY sd.created_at DESC")->fetchAll();

// Paginación y búsqueda de usuarios con Modelo Usuario
$pagina_usuarios = max(1, (int)($_GET['pag_u'] ?? 1));
$por_pagina_usuarios = 15;
$busqueda_usuarios = trim((string)($_GET['q_u'] ?? ''));
$total_usuarios_filtrados = Usuario::contarFiltrados($pdo, $busqueda_usuarios ?: null);
$usuarios_lista = Usuario::listarPaginado($pdo, $pagina_usuarios, $por_pagina_usuarios, $busqueda_usuarios ?: null);

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

// Actas definitivas
$actas_cerradas = $pdo->query("SELECT an.*, u.nombres, u.apellidos, asig.nombre as materia, sec.seccion
                                FROM actas_notas an
                                JOIN usuarios u ON u.id = an.usuario_id
                                JOIN secciones sec ON sec.id = an.seccion_id
                                JOIN asignaturas asig ON asig.codigo = sec.asignatura_codigo
                                WHERE an.estatus = 'Definitiva'
                                ORDER BY an.updated_at DESC LIMIT 20")->fetchAll();

// Preguntas del baremo (A través de Modelo Baremo)
$baremo_preguntas = Baremo::listarPreguntas($pdo);

$titulo = 'Panel de Administración';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">UNEFA &middot; Admin</span>
            </div>
            <p>Admin: <?php echo h($nombre_admin); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active" data-modulo="inicio"><span>🏠 Visión General</span></a>
            <a href="#modulo-usuarios" data-modulo="usuarios"><span>👥 Usuarios y Roles</span></a>
            <a href="#modulo-sedes" data-modulo="sedes"><span>🌐 Sedes y Núcleos</span></a>
            <a href="#modulo-solicitudes" data-modulo="solicitudes"><span>📨 Solicitudes Docentes (<?php echo count($solicitudes); ?>)</span></a>
            <a href="#modulo-actas" data-modulo="actas"><span>🔓 Reabrir Actas</span></a>
            <a href="#modulo-auditoria" data-modulo="auditoria"><span>📊 Auditoría del Sistema</span></a>
            <a href="#modulo-baremo" data-modulo="baremo"><span>📋 Baremo Digital</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div>
                <h2>Panel Maestro de Administración y Gobernanza</h2>
                <p><?php echo fecha_hoy_formateada(); ?> &middot; Entorno de Producción Seguro</p>
            </div>
            <div>
                <span class="badge badge-success" style="font-size:0.85rem;padding:6px 14px;">🟢 SISTEMA EN LÍNEA</span>
            </div>
        </header>

        <?php echo $mensaje; ?>
        <?php echo render_flash(); ?>

        <!-- KPI METRICS WIDGETS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $totalUsuarios; ?></span>
                    <span class="stat-lbl">Usuarios Totales</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">🎓</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $totalEstudiantes; ?></span>
                    <span class="stat-lbl">Estudiantes Regulares</span>
                </div>
            </div>
            <div class="stat-card gold">
                <div class="stat-icon">📝</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $totalAspirantes; ?></span>
                    <span class="stat-lbl">Aspirantes Registrados</span>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">🏛️</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $totalSedes; ?></span>
                    <span class="stat-lbl">Sedes Operativas</span>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">📚</div>
                <div class="stat-details">
                    <span class="stat-val"><?php echo $totalSecciones; ?></span>
                    <span class="stat-lbl">Secciones Abiertas</span>
                </div>
            </div>
        </div>

        <!-- ===== MÓDULO: GESTIÓN DE USUARIOS ===== -->
        <section id="modulo-usuarios" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>👥 Directorio y Creación de Usuarios</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <a href="<?php echo h(asset_url('controlador/reportes.php')); ?>?tipo=csv_usuarios" class="btn-outline btn-sm" title="Descargar CSV con todos los usuarios" download>
                            📥 Exportar CSV
                        </a>
                        <button class="btn-primary btn-sm" onclick="toggleForm('form-crear-usuario')">➕ Registrar Nuevo Usuario</button>
                    </div>
                </div>
                <div class="dashboard-card-body">
                    <!-- Formulario de nuevo usuario colapsable -->
                    <div id="form-crear-usuario" style="display:none;padding:20px;border-radius:12px;margin-bottom:24px;border:1px solid var(--border-light);background:var(--bg-glass);">
                        <h4 style="margin-bottom:16px;color:var(--unefa-navy);">Crear Usuario con Rol Especial</h4>
                        <form method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="crear_usuario" value="1">
                            <div class="form-grid-3">
                                <div class="input-group">
                                    <label>Tipo Documento:</label>
                                    <select name="tipo_documento" required>
                                        <option value="V">V - Venezolano</option>
                                        <option value="E">E - Extranjero</option>
                                        <option value="P">P - Pasaporte</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Número de Documento:</label>
                                    <input type="text" name="numero_documento" placeholder="Ej: 12345678" required>
                                </div>
                                <div class="input-group">
                                    <label>Rol en el Sistema:</label>
                                    <select name="rol_id" required>
                                        <option value="1">1 - Administrador</option>
                                        <option value="2">2 - Coordinador de Programa</option>
                                        <option value="3">3 - Docente</option>
                                        <option value="4">4 - Secretaría / Control Estudios</option>
                                        <option value="5">5 - Aspirante</option>
                                        <option value="6">6 - Estudiante Regular</option>
                                        <option value="7">7 - Director de Postgrado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-grid-3">
                                <div class="input-group">
                                    <label>Nombres:</label>
                                    <input type="text" name="nombres" placeholder="Nombres" required>
                                </div>
                                <div class="input-group">
                                    <label>Apellidos:</label>
                                    <input type="text" name="apellidos" placeholder="Apellidos" required>
                                </div>
                                <div class="input-group">
                                    <label>Correo Electrónico:</label>
                                    <input type="email" name="email" placeholder="correo@ejemplo.com" required>
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="input-group">
                                    <label>Contraseña de Acceso:</label>
                                    <input type="password" name="password" placeholder="Mínimo 8 caracteres" required>
                                </div>
                                <div class="input-group">
                                    <label>Sede Asignada:</label>
                                    <select name="sede_id">
                                        <option value="">(Sin sede específica / Global)</option>
                                        <?php foreach ($sedes as $s): ?>
                                            <option value="<?php echo $s['id']; ?>"><?php echo h($s['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-top:16px;display:flex;gap:12px;">
                                <button type="submit" class="btn-primary">Guardar Usuario</button>
                                <button type="button" class="btn-outline" onclick="toggleForm('form-crear-usuario')">Cancelar</button>
                            </div>
                        </form>
                    </div>

                    <!-- Buscador en tabla -->
                    <div class="toolbar-actions">
                        <form method="GET" action="#modulo-usuarios" class="search-input-wrapper" style="width:100%;max-width:420px;display:flex;gap:6px;">
                            <span class="search-icon">🔍</span>
                            <input type="text" name="q_u" value="<?php echo h($busqueda_usuarios); ?>" placeholder="Buscar por nombre, cédula o email...">
                            <?php if ($busqueda_usuarios !== ''): ?>
                                <a href="dashboard.php#modulo-usuarios" class="btn btn-sm btn-outline" style="text-decoration:none;white-space:nowrap;">Limpiar</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="tabla-usuarios">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Documento</th>
                                    <th>Nombre Completo</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Sede</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios_lista as $u): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><strong><?php echo h($u['tipo_cedula'] . '-' . $u['numero_documento']); ?></strong></td>
                                    <td><?php echo h($u['nombres'] . ' ' . $u['apellidos']); ?></td>
                                    <td><?php echo h($u['email']); ?></td>
                                    <td><span class="badge badge-navy"><?php echo h($u['rol_nombre'] ?: obtener_nombre_rol($u['rol_id'])); ?></span></td>
                                    <td><?php echo h($u['sede_nombre'] ?: '—'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $u['estatus'] === 'Activo' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo h($u['estatus']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['id'] !== (int)$_SESSION['usuario_id']): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="cambiar_estatus_usuario" value="1">
                                            <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="estatus" value="<?php echo $u['estatus']; ?>">
                                            <button type="submit" class="btn-outline btn-sm" onclick="return confirm('¿Confirma cambiar estatus de esta cuenta?')">
                                                <?php echo $u['estatus'] === 'Activo' ? '🚫 Bloquear' : '✅ Desbloquear'; ?>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <span style="font-size:0.75rem;color:#888;">(Tu cuenta)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo render_paginacion($total_usuarios_filtrados, $pagina_usuarios, $por_pagina_usuarios, 'pag_u'); ?>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: SEDES Y NÚCLEOS ===== -->
        <section id="modulo-sedes" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>🌐 Gestión de Sedes / Núcleos de Postgrado</h3>
                </div>
                <div class="dashboard-card-body">
                    <form method="POST" style="background:#f8fafc;padding:16px;border-radius:10px;margin-bottom:20px;">
                        <?php echo csrf_field(); ?>
                        <div class="form-grid-3">
                            <div class="input-group">
                                <label>Nombre de la Sede</label>
                                <input type="text" name="sede_nombre" placeholder="Ej: Núcleo Carabobo" required>
                            </div>
                            <div class="input-group">
                                <label>Ubicación Geográfica</label>
                                <input type="text" name="sede_ubicacion" placeholder="Ej: Valencia, Edo. Carabobo">
                            </div>
                            <div class="input-group">
                                <label>Código Identificador</label>
                                <input type="text" name="sede_codigo" placeholder="Ej: CBB" required maxlength="10">
                            </div>
                        </div>
                        <button type="submit" name="crear_sede" class="btn-primary">➕ Registrar Sede</button>
                    </form>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Código</th><th>Nombre de la Sede</th><th>Ubicación</th><th>Fase de Sede</th><th>Directores</th><th>Estatus</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sedes as $s): ?>
                                <tr>
                                    <td><strong><?php echo h($s['codigo']); ?></strong></td>
                                    <td><?php echo h($s['nombre']); ?></td>
                                    <td><?php echo h($s['ubicacion']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $s['fase_actual']==1?'badge-warning':'badge-success'; ?>">
                                            Fase <?php echo $s['fase_actual']; ?>: <?php echo $s['fase_actual']==1 ? 'Planificación/Admisión' : 'Inscripción y Clases'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $s['directores']; ?> asignado(s)</td>
                                    <td><?php echo $s['activa'] ? '🟢 Activa' : '🔴 Inactiva'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: SOLICITUDES DOCENTES ===== -->
        <section id="modulo-solicitudes" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📨 Solicitudes Urgentes de Docentes Emitidas por Coordinadores</h3>
                </div>
                <div class="dashboard-card-body">
                    <?php if (count($solicitudes) === 0): ?>
                        <div class="alert alert-info">✅ No hay solicitudes docentes pendientes de aprobación.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Fecha</th><th>Sede Solicitante</th><th>Identidad</th><th>Nombres y Apellidos</th><th>Correo</th><th>Acción</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudes as $sol): ?>
                                <tr>
                                    <td><?php echo formatear_fecha($sol['created_at']); ?></td>
                                    <td><strong><?php echo h($sol['sede_nombre']); ?></strong></td>
                                    <td><?php echo h($sol['tipo_documento'] . '-' . $sol['numero_documento']); ?></td>
                                    <td><?php echo h($sol['nombres'] . ' ' . $sol['apellidos']); ?></td>
                                    <td><?php echo h($sol['email']); ?></td>
                                    <td>
                                        <form method="POST">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="solicitud_id" value="<?php echo $sol['id']; ?>">
                                            <button type="submit" name="aprobar_solicitud" class="btn-success btn-sm">
                                                ✅ Crear y Activar Docente
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: REAPERTURA DE ACTAS ===== -->
        <section id="modulo-actas" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>🔓 Llave Maestra: Reapertura de Actas Definitivas</h3>
                </div>
                <div class="dashboard-card-body">
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:16px;">
                        Permite a la Dirección de Administración habilitar temporalmente la edición de notas de un acta ya cerrada para la rectificación de calificaciones por parte del docente.
                    </p>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Asignatura</th><th>Sección</th><th>Estudiante</th><th>Nota Definitiva</th><th>Fecha Cierre</th><th>Acción</th></tr>
                            </thead>
                            <tbody>
                                <?php if (count($actas_cerradas) === 0): ?>
                                <tr><td colspan="6" class="text-center">No hay actas definitivas cerradas recientemente.</td></tr>
                                <?php else: ?>
                                <?php foreach ($actas_cerradas as $acta): ?>
                                <tr>
                                    <td><strong><?php echo h($acta['materia']); ?></strong></td>
                                    <td>Sec. <?php echo h($acta['seccion']); ?></td>
                                    <td><?php echo h($acta['nombres'] . ' ' . $acta['apellidos']); ?></td>
                                    <td><strong><?php echo $acta['nota']; ?> / 20</strong></td>
                                    <td><?php echo formatear_fecha($acta['updated_at']); ?></td>
                                    <td>
                                        <form method="POST">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="acta_id" value="<?php echo $acta['id']; ?>">
                                            <button type="submit" name="habilitar_acta" class="btn-warning btn-sm" onclick="return confirm('¿Seguro que desea reabrir esta acta para edición?')">
                                                🔓 Reabrir para Edición
                                            </button>
                                        </form>
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

        <!-- ===== MÓDULO: AUDITORÍA ===== -->
        <section id="modulo-auditoria" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📊 Registro de Trazabilidad y Auditoría Forense</h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="toolbar-actions">
                        <div class="search-input-wrapper">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="filtro-logs" placeholder="Filtrar eventos o usuarios..." onkeyup="filtrarTabla('filtro-logs', 'tabla-logs')">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table" id="tabla-logs">
                            <thead>
                                <tr><th>Fecha / Hora</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>Detalle Técnico</th><th>IP</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td style="white-space:nowrap;font-size:0.75rem;"><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td><strong><?php echo h(($log['nombres'] ?? '') . ' ' . ($log['apellidos'] ?? 'Sistema')); ?></strong></td>
                                    <td><span class="badge badge-navy"><?php echo h($log['accion']); ?></span></td>
                                    <td><?php echo h($log['entidad']); ?></td>
                                    <td><?php echo h(escapar_texto_multilinea($log['detalle'] ?? '')); ?></td>
                                    <td><code><?php echo h($log['direccion_ip'] ?? '127.0.0.1'); ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== MÓDULO: BAREMO DIGITAL ===== -->
        <section id="modulo-baremo" class="module-section">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📋 Baremo Digital: Gestión y Modificación de Preguntas</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn-primary btn-sm" onclick="toggleForm('form-crear-pregunta')">➕ Nueva Pregunta</button>
                    </div>
                </div>
                <div class="dashboard-card-body">
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:18px;">
                        Configure las preguntas, criterios evaluativos, categorías y orden del Baremo Digital institucional utilizado para la evaluación de aspirantes y docentes.
                    </p>

                    <!-- Formulario desplegable para nueva pregunta -->
                    <div id="form-crear-pregunta" style="display:none;padding:20px;border-radius:12px;margin-bottom:24px;border:1px solid var(--border-light);background:var(--bg-glass);">
                        <h4 style="margin-bottom:14px;color:var(--unefa-navy);">➕ Agregar Nueva Pregunta al Baremo</h4>
                        <form method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="crear_pregunta" value="1">
                            <div class="form-grid-3">
                                <div class="input-group" style="grid-column: span 2;">
                                    <label>Pregunta / Criterio:</label>
                                    <input type="text" name="pregunta" placeholder="Ej: ¿Posee publicaciones en revistas indexadas?" required>
                                </div>
                                <div class="input-group">
                                    <label>Categoría:</label>
                                    <input type="text" name="categoria" list="lista-categorias-baremo" placeholder="Ej: Académica" required>
                                    <datalist id="lista-categorias-baremo">
                                        <option value="Académica">
                                        <option value="Experiencia Docente">
                                        <option value="Investigación">
                                        <option value="Producción Intelectual">
                                        <option value="General">
                                    </datalist>
                                </div>
                                <div class="input-group">
                                    <label>Orden:</label>
                                    <input type="number" name="orden" value="<?php echo count($baremo_preguntas) + 1; ?>" min="1" required>
                                </div>
                            </div>
                            <div style="margin-top:14px;display:flex;gap:10px;">
                                <button type="submit" class="btn-primary btn-sm">Guardar Pregunta</button>
                                <button type="button" class="btn-secondary btn-sm" onclick="toggleForm('form-crear-pregunta')">Cancelar</button>
                            </div>
                        </form>
                    </div>

                    <div class="toolbar-actions">
                        <div class="search-input-wrapper">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="filtro-baremo" placeholder="Buscar preguntas por texto o categoría..." onkeyup="filtrarTabla('filtro-baremo', 'tabla-baremo')">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="tabla-baremo">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th style="width:180px;">Categoría</th>
                                    <th style="width:70px;text-align:center;">Orden</th>
                                    <th>Pregunta / Criterio Evaluado</th>
                                    <th style="width:160px;text-align:center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($baremo_preguntas) === 0): ?>
                                <tr><td colspan="5" class="text-center">No hay preguntas registradas en el Baremo Digital.</td></tr>
                                <?php else: ?>
                                <?php foreach ($baremo_preguntas as $bp): ?>
                                <tr>
                                    <td><strong><?php echo (int)$bp['id']; ?></strong></td>
                                    <td><span class="badge badge-navy"><?php echo h($bp['categoria']); ?></span></td>
                                    <td style="text-align:center;"><strong><?php echo (int)($bp['orden'] ?? 1); ?></strong></td>
                                    <td><?php echo h($bp['pregunta']); ?></td>
                                    <td style="text-align:center;">
                                        <div style="display:inline-flex;gap:6px;">
                                            <button type="button" class="btn-warning btn-sm" onclick="abrirModalEditarPregunta(<?php echo (int)$bp['id']; ?>, <?php echo htmlspecialchars(json_encode($bp['pregunta']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($bp['categoria']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo (int)($bp['orden'] ?? 1); ?>)" title="Modificar pregunta">
                                                ✏️ Modificar
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que desea eliminar esta pregunta del Baremo? Las respuestas asociadas se limpiarán.');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="eliminar_pregunta" value="1">
                                                <input type="hidden" name="pregunta_id" value="<?php echo (int)$bp['id']; ?>">
                                                <button type="submit" class="btn-danger btn-sm" title="Eliminar">🗑️</button>
                                            </form>
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

        <!-- MODAL PARA MODIFICAR PREGUNTA DEL BAREMO -->
        <div id="modal-editar-pregunta" class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
            <div class="modal-container" style="background:var(--bg-card);border:1px solid var(--border-light);border-radius:16px;width:90%;max-width:580px;box-shadow:0 20px 40px rgba(0,0,0,0.4);overflow:hidden;">
                <div style="padding:18px 24px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:1.1rem;color:var(--unefa-navy);" id="modal-preg-title">✏️ Modificar Pregunta del Baremo</h3>
                    <button type="button" onclick="cerrarModalEditarPregunta()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--text-muted);">&times;</button>
                </div>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="editar_pregunta" value="1">
                    <input type="hidden" id="edit-preg-id" name="pregunta_id" value="">
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        <div class="input-group">
                            <label style="font-weight:600;margin-bottom:6px;display:block;">Texto / Enunciado de la Pregunta:</label>
                            <textarea id="edit-preg-texto" name="pregunta" rows="3" required style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border-light);font-family:inherit;font-size:0.9rem;resize:vertical;"></textarea>
                        </div>
                        <div class="form-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="input-group">
                                <label style="font-weight:600;margin-bottom:6px;display:block;">Categoría:</label>
                                <input type="text" id="edit-preg-cat" name="categoria" list="lista-categorias-baremo" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid var(--border-light);">
                            </div>
                            <div class="input-group">
                                <label style="font-weight:600;margin-bottom:6px;display:block;">Número de Orden:</label>
                                <input type="number" id="edit-preg-orden" name="orden" min="1" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid var(--border-light);">
                            </div>
                        </div>
                    </div>
                    <div style="padding:16px 24px;border-top:1px solid var(--border-light);display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" class="btn-secondary btn-sm" onclick="cerrarModalEditarPregunta()">Cancelar</button>
                        <button type="submit" class="btn-primary btn-sm">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}

function filtrarTabla(inputId, tableId) {
    const query = document.getElementById(inputId).value.toLowerCase();
    const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
    rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        r.style.display = text.includes(query) ? '' : 'none';
    });
}

function abrirModalEditarPregunta(id, pregunta, categoria, orden) {
    document.getElementById('edit-preg-id').value = id;
    document.getElementById('edit-preg-texto').value = pregunta;
    document.getElementById('edit-preg-cat').value = categoria;
    document.getElementById('edit-preg-orden').value = orden;
    document.getElementById('modal-preg-title').textContent = '✏️ Modificar Pregunta #' + id;
    const modal = document.getElementById('modal-editar-pregunta');
    modal.style.display = 'flex';
}

function cerrarModalEditarPregunta() {
    const modal = document.getElementById('modal-editar-pregunta');
    modal.style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
