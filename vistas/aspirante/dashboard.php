<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(5);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

iniciar_sesion_segura();

$usuario_id = (int)$_SESSION['usuario_id'];
$nombre_asp = $_SESSION['nombre_full'];
$mensaje = '';

// Obtener datos del aspirante
$stmt = $pdo->prepare("SELECT u.*, s.nombre as sede_nombre, pl.nombre as plan_nombre, pl.codigo as plan_codigo
                       FROM usuarios u
                       LEFT JOIN sedes s ON s.id = u.sede_id
                       LEFT JOIN plan_estudios pl ON pl.id = u.plan_id
                       WHERE u.id = :id");
$stmt->execute([':id' => $usuario_id]);
$usuario = $stmt->fetch();

$estado = $usuario['estado_aspirante'] ?? 'En Revision Digital';

// Procesar envío de postulación y recaudos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_postulacion'])) {
    if (!validar_csrf()) {
        $mensaje = alerta_error('Token de seguridad inválido. Recargue la página.');
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Guardar Sede y Programa de Postgrado deseado
            $nueva_sede = !empty($_POST['sede_id']) ? (int)$_POST['sede_id'] : null;
            $nuevo_plan = !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;
            $tema_interes = trim($_POST['tema_interes'] ?? '');

            $stmtUpd = $pdo->prepare("UPDATE usuarios SET 
                                        sede_id = COALESCE(:sede, sede_id),
                                        plan_id = COALESCE(:plan, plan_id),
                                        direccion = CASE WHEN :dir != '' THEN :dir ELSE direccion END
                                      WHERE id = :id");
            $stmtUpd->execute([
                ':sede' => $nueva_sede,
                ':plan' => $nuevo_plan,
                ':dir'  => $tema_interes !== '' ? "Línea de investigación: $tema_interes" : '',
                ':id'   => $usuario_id,
            ]);

            if ($nueva_sede) {
                $_SESSION['sede_id'] = $nueva_sede;
            }

            // 2. Guardar respuestas del baremo
            if (isset($_POST['baremo']) && is_array($_POST['baremo'])) {
                $stmtDel = $pdo->prepare("DELETE FROM respuestas_baremo WHERE id_aspirante = :uid");
                $stmtDel->execute([':uid' => $usuario_id]);

                $stmtIns = $pdo->prepare("INSERT INTO respuestas_baremo (id_aspirante, id_pregunta, respuesta) VALUES (:uid, :pid, :res)");
                foreach ($_POST['baremo'] as $id_pregunta => $respuesta) {
                    $stmtIns->execute([':uid' => $usuario_id, ':pid' => (int)$id_pregunta, ':res' => ($respuesta === 'si' ? 'si' : 'no')]);
                }
            }

            // 3. Procesar subida de documentos con verificación MIME real
            $upload_dir = __DIR__ . '/../../uploads/documentos/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $tipos_permitidos = ['Cedula' => 'cedula', 'Pasaporte' => 'pasaporte', 'Titulo' => 'titulo', 'Notas' => 'notas', 'Curriculum' => 'curriculum'];

            $archivosSubidos = 0;
            foreach ($tipos_permitidos as $tipo_db => $input_name) {
                if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                    $valArchivo = validar_archivo_subido($_FILES[$input_name], ['application/pdf', 'image/jpeg', 'image/png'], 8);
                    if (!$valArchivo['valido']) {
                        continue;
                    }

                    $archivo = $_FILES[$input_name];
                    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                    $nombre_seguro = bin2hex(random_bytes(8));
                    $nombre_unico = $usuario_id . '_' . $tipo_db . '_' . $nombre_seguro . '.' . $ext;
                    $ruta_destino = $upload_dir . $nombre_unico;

                    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                        $stmtCheck = $pdo->prepare("SELECT id FROM aspirante_documentos WHERE usuario_id = :uid AND tipo = :tipo");
                        $stmtCheck->execute([':uid' => $usuario_id, ':tipo' => $tipo_db]);
                        $existe = $stmtCheck->fetch();

                        if ($existe) {
                            $stmtUpd = $pdo->prepare("UPDATE aspirante_documentos SET archivo_ruta = :ruta, archivo_nombre = :nom, verificado = false WHERE id = :id");
                            $stmtUpd->execute([':ruta' => 'uploads/documentos/' . $nombre_unico, ':nom' => basename($archivo['name']), ':id' => $existe['id']]);
                        } else {
                            $stmtIns = $pdo->prepare("INSERT INTO aspirante_documentos (usuario_id, tipo, archivo_ruta, archivo_nombre, verificado) VALUES (:uid, :tipo, :ruta, :nom, false)");
                            $stmtIns->execute([':uid' => $usuario_id, ':tipo' => $tipo_db, ':ruta' => 'uploads/documentos/' . $nombre_unico, ':nom' => basename($archivo['name'])]);
                        }
                        $archivosSubidos++;
                        registrar_log($pdo, 'Subir recaudo aspirante', 'aspirante_documentos', $usuario_id, "Tipo: $tipo_db");
                    }
                }
            }

            // Cambiar a En Revision Digital
            if ($estado === 'Con Observaciones' && $archivosSubidos > 0) {
                $pdo->prepare("UPDATE usuarios SET estado_aspirante = 'En Revision Digital' WHERE id = :id")->execute([':id' => $usuario_id]);
                $estado = 'En Revision Digital';
            }

            $pdo->commit();
            registrar_log($pdo, 'Actualizar postulación', 'usuarios', $usuario_id);
            $mensaje = alerta_success('¡Postulación y documentos actualizados con éxito! Su expediente está en proceso de revisión por Control de Estudios.');

            // Recargar datos actualizados
            $stmt->execute([':id' => $usuario_id]);
            $usuario = $stmt->fetch();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('[SIP] Error en postulación: ' . $e->getMessage());
            $mensaje = alerta_error('Ocurrió un error al procesar el expediente: ' . $e->getMessage());
        }
    }
}

// Obtener documentos subidos
$documentos = $pdo->prepare("SELECT * FROM aspirante_documentos WHERE usuario_id = :uid");
$documentos->execute([':uid' => $usuario_id]);
$documentos = $documentos->fetchAll();

// Obtener respuestas del baremo
$baremo_respuestas = $pdo->prepare("SELECT r.*, p.pregunta, p.categoria FROM respuestas_baremo r
                                     JOIN baremo_preguntas p ON p.id = r.id_pregunta
                                     WHERE r.id_aspirante = :uid");
$baremo_respuestas->execute([':uid' => $usuario_id]);
$baremo = $baremo_respuestas->fetchAll();

$puntaje_baremo = 0;
foreach ($baremo as $b) {
    if ($b['respuesta'] === 'si') $puntaje_baremo++;
}

// Preguntas del baremo
$preguntas = $pdo->query("SELECT * FROM baremo_preguntas ORDER BY categoria, orden")->fetchAll();
$preguntas_por_categoria = [];
foreach ($preguntas as $p) {
    $preguntas_por_categoria[$p['categoria']][] = $p;
}

// Catálogos para selección
$sedes_catalogo = $pdo->query("SELECT id, nombre, ubicacion FROM sedes WHERE activa = true ORDER BY nombre")->fetchAll();
$planes_catalogo = $pdo->query("SELECT id, nombre, tipo, codigo FROM plan_estudios WHERE activo = true ORDER BY nombre")->fetchAll();

$titulo = 'Expediente de Admisión';
require_once __DIR__ . '/../../includes/template_header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img class="logo-img" src="<?php echo obtener_ruta_base(); ?>imagenes/LOGO-1-1.png" alt="UNEFA">
            <div class="sidebar-brand">
                <h3>SIP-Postgrado</h3>
                <span class="brand-sub">Postulante</span>
            </div>
            <p><?php echo h($nombre_asp); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active"><span>📝 Mi Postulación</span></a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn"><span>🚪 Cerrar Sesión</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div>
                <h2>Expediente Virtual de Admisión a Postgrado</h2>
                <p><?php echo fecha_hoy_formateada(); ?> &middot; Convocatoria Académica</p>
            </div>
            <div>
                <?php if ($estado === 'Admitido'): ?>
                    <span class="badge badge-success" style="font-size:0.9rem;padding:8px 16px;">🟢 ADMITIDO FORMALMENTE</span>
                <?php elseif ($estado === 'Con Observaciones'): ?>
                    <span class="badge badge-danger" style="font-size:0.9rem;padding:8px 16px;">🔴 CON OBSERVACIONES</span>
                <?php else: ?>
                    <span class="badge badge-warning" style="font-size:0.9rem;padding:8px 16px;">🟡 EN REVISIÓN DIGITAL</span>
                <?php endif; ?>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <!-- BANNER DE ESTADO INFORMATIVO -->
        <?php if ($estado === 'Admitido'): ?>
        <div class="dashboard-card" style="background: linear-gradient(135deg, #001a57, #1e6091); color:#fff; padding:24px; border-radius:16px; margin-bottom:24px;">
            <h3 style="color:#d4af37; margin-bottom:10px; font-size:1.4rem;">🎉 ¡Felicidades! Ha sido Admitido en el Programa de Postgrado</h3>
            <p style="font-size:0.95rem; line-height:1.6; opacity:0.95;">
                Su expediente y méritos académicos han sido verificados satisfactoriamente por el Consejo de Postgrado de la UNEFA.<br>
                Ya tiene habilitado el estatus de <strong>Estudiante Regular</strong>. Ahora puede descargar su constancia oficial o ingresar a la selección de asignaturas.
            </p>
            <div style="margin-top:16px;">
                <a href="../../controlador/reportes.php?tipo=constancia_estudio" target="_blank" class="btn-primary" style="background:#d4af37; color:#001a57; font-weight:bold;">
                    📜 Descargar Dictamen Oficial de Admisión
                </a>
            </div>
        </div>
        <?php elseif ($estado === 'Con Observaciones'): ?>
        <div class="alert alert-warning" style="font-size:0.9rem; padding:16px; margin-bottom:24px;">
            <strong>⚠️ Atención Requerida:</strong> La Secretaría de Control de Estudios ha detectado observaciones en sus recaudos. Por favor revise los documentos señalados a continuación y suba los archivos corregidos en formato PDF o JPG legible.
        </div>
        <?php endif; ?>

        <!-- BAREMO SCORE BANNER -->
        <div class="baremo-resultado">
            <div class="baremo-score-total"><?php echo $puntaje_baremo; ?><small> / <?php echo max(1, count($preguntas)); ?></small></div>
            <div>
                <div class="score-label">Puntaje Preliminar de Méritos</div>
                <small style="opacity:0.8;">Calculado automáticamente a partir del cuestionario académico digital.</small>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <!-- PASO 1: ELECCIÓN DE PROGRAMA Y SEDE -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>🏛️ Programa de Postgrado y Núcleo de Destino</h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Sede / Núcleo:</label>
                            <select name="sede_id" required <?php echo $estado === 'Admitido' ? 'disabled' : ''; ?>>
                                <option value="">Seleccione Sede</option>
                                <?php foreach ($sedes_catalogo as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo ((int)($usuario['sede_id'] ?? 0) === (int)$s['id']) ? 'selected' : ''; ?>>
                                    <?php echo h($s['nombre']); ?> (<?php echo h($s['ubicacion']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Programa Académico al que Postula:</label>
                            <select name="plan_id" required <?php echo $estado === 'Admitido' ? 'disabled' : ''; ?>>
                                <option value="">Seleccione Programa</option>
                                <?php foreach ($planes_catalogo as $pl): ?>
                                <option value="<?php echo $pl['id']; ?>" <?php echo ((int)($usuario['plan_id'] ?? 0) === (int)$pl['id']) ? 'selected' : ''; ?>>
                                    <?php echo h($pl['tipo']); ?>: <?php echo h($pl['nombre']); ?> (<?php echo h($pl['codigo']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Tema o Línea de Investigación de Interés Preliminar:</label>
                        <input type="text" name="tema_interes" placeholder="Ej: Optimización de cadenas de suministro mediante sistemas inteligentes"
                               value="<?php echo h(str_replace('Línea de investigación: ', '', $usuario['direccion'] ?? '')); ?>"
                               <?php echo $estado === 'Admitido' ? 'readonly' : ''; ?>>
                    </div>
                </div>
            </div>

            <!-- PASO 2: CUESTIONARIO BAREMO -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📊 Cuestionario de Méritos Académicos (Baremo Digital)</h3>
                </div>
                <div class="dashboard-card-body">
                    <?php foreach ($preguntas_por_categoria as $categoria => $pregs): ?>
                    <h4 style="color:#001a57; margin: 16px 0 10px; border-bottom:1px solid #e2e8f0; padding-bottom:4px; font-size:0.95rem;">
                        <?php echo h($categoria); ?>
                    </h4>
                    <?php foreach ($pregs as $p): ?>
                        <?php
                        $resp_existente = 'no';
                        foreach ($baremo as $b) {
                            if ((int)$b['id_pregunta'] === (int)$p['id']) {
                                $resp_existente = $b['respuesta'];
                                break;
                            }
                        }
                        ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px dashed #f1f5f9; gap:16px;">
                            <label style="font-size:0.85rem; color:#1e293b; margin:0; flex:1;">
                                <strong><?php echo $p['orden']; ?>.</strong> <?php echo h($p['pregunta']); ?>
                            </label>
                            <div style="display:flex; gap:16px; flex-shrink:0;">
                                <label style="font-size:0.85rem; cursor:pointer;">
                                    <input type="radio" name="baremo[<?php echo $p['id']; ?>]" value="si" <?php echo $resp_existente === 'si' ? 'checked' : ''; ?> <?php echo $estado === 'Admitido' ? 'disabled' : ''; ?>> Sí
                                </label>
                                <label style="font-size:0.85rem; cursor:pointer;">
                                    <input type="radio" name="baremo[<?php echo $p['id']; ?>]" value="no" <?php echo $resp_existente === 'no' ? 'checked' : ''; ?> <?php echo $estado === 'Admitido' ? 'disabled' : ''; ?>> No
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PASO 3: RECAUDOS Y DOCUMENTOS VIRTUALES -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>📁 Carga Digital de Recaudos Obligatorios</h3>
                </div>
                <div class="dashboard-card-body">
                    <p style="color:#666; font-size:0.85rem; margin-bottom:16px;">
                        Formatos permitidos: <strong>PDF, JPG o PNG</strong>. Tamaño máximo por archivo: <strong>8MB</strong>. Asegúrese de que los textos y sellos sean completamente legibles.
                    </p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
                        <?php
                        $tipos_docs = [
                            'Cedula'     => ['label' => 'Cédula de Identidad / DNI', 'input' => 'cedula'],
                            'Pasaporte'  => ['label' => 'Pasaporte (Extranjeros)', 'input' => 'pasaporte'],
                            'Titulo'     => ['label' => 'Fondo Negro o Copia del Título de Pregrado', 'input' => 'titulo'],
                            'Notas'      => ['label' => 'Certificación de Calificaciones de Pregrado', 'input' => 'notas'],
                            'Curriculum' => ['label' => 'Síntesis Curricular Actualizada', 'input' => 'curriculum'],
                        ];
                        foreach ($tipos_docs as $tipo => $cfg):
                            $doc_subido = false;
                            $doc_id = 0;
                            foreach ($documentos as $d) {
                                if ($d['tipo'] === $tipo) {
                                    $doc_subido = true;
                                    $doc_id = $d['id'];
                                    break;
                                }
                            }
                        ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <strong><?php echo h($cfg['label']); ?></strong>
                                <?php if ($doc_subido): ?>
                                    <span class="badge badge-success">✓ Subido</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pendiente</span>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="<?php echo $cfg['input']; ?>" accept=".pdf,.jpg,.jpeg,.png"
                                   style="font-size:0.82rem; width:100%;"
                                   <?php echo ($estado === 'Admitido') ? 'disabled' : ''; ?>>
                            <?php if ($doc_subido): ?>
                                <div style="margin-top:8px;">
                                    <a href="../../controlador/descargar_documento.php?id=<?php echo $doc_id; ?>" target="_blank" style="font-size:0.78rem; color:#0f4c81; text-decoration:underline;">
                                        👁️ Ver archivo subido
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if ($estado !== 'Admitido'): ?>
            <div style="display:flex; justify-content:flex-end; margin-bottom:40px;">
                <button type="submit" name="enviar_postulacion" class="btn-primary" style="padding:12px 28px; font-size:1rem;"
                        onclick="return confirm('¿Confirma enviar sus recaudos y baremo a revisión digital?')">
                    📨 Guardar y Enviar a Revisión de Admisión
                </button>
            </div>
            <?php endif; ?>
        </form>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
