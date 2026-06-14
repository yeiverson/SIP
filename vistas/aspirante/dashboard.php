<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/functions.php';
check_rol(5);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/logs.php';

$usuario_id = $_SESSION['usuario_id'];
$nombre_asp = $_SESSION['nombre_full'];

// Obtener datos del aspirante
$stmt = $pdo->prepare("SELECT u.*, s.nombre as sede_nombre FROM usuarios u LEFT JOIN sedes s ON s.id = u.sede_id WHERE u.id = :id");
$stmt->execute([':id' => $usuario_id]);
$usuario = $stmt->fetch();

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

// Puntaje del baremo
$puntaje_baremo = 0;
foreach ($baremo as $b) {
    if ($b['respuesta'] === 'si') $puntaje_baremo++;
}

$estado = $usuario['estado_aspirante'] ?? 'En Revision Digital';

// Procesar envío de postulación completa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_postulacion'])) {
    try {
        $pdo->beginTransaction();

        // Guardar respuestas del baremo
        if (isset($_POST['baremo']) && is_array($_POST['baremo'])) {
            $stmtDel = $pdo->prepare("DELETE FROM respuestas_baremo WHERE id_aspirante = :uid");
            $stmtDel->execute([':uid' => $usuario_id]);

            $stmtIns = $pdo->prepare("INSERT INTO respuestas_baremo (id_aspirante, id_pregunta, respuesta) VALUES (:uid, :pid, :res)");
            foreach ($_POST['baremo'] as $id_pregunta => $respuesta) {
                $stmtIns->execute([':uid' => $usuario_id, ':pid' => $id_pregunta, ':res' => $respuesta]);
            }
        }

        // Guardar tema de interés
        if (!empty($_POST['tema_interes'])) {
            $stmtUpd = $pdo->prepare("UPDATE usuarios SET direccion = :dir WHERE id = :id");
            $stmtUpd->execute([':dir' => 'Tema de interés: ' . $_POST['tema_interes'], ':id' => $usuario_id]);
        }

        // Procesar subida de documentos
        $upload_dir = __DIR__ . '/../../uploads/documentos/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $tipos_permitidos = ['Cedula' => 'cedula', 'Pasaporte' => 'pasaporte', 'Titulo' => 'titulo', 'Notas' => 'notas', 'Curriculum' => 'curriculum'];

        foreach ($tipos_permitidos as $tipo_db => $input_name) {
            if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES[$input_name];
                $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) continue;
                if ($archivo['size'] > 5 * 1024 * 1024) continue;

                $nombre_unico = $usuario_id . '_' . $tipo_db . '_' . time() . '.' . $ext;
                $ruta_destino = $upload_dir . $nombre_unico;

                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    $stmtCheck = $pdo->prepare("SELECT id FROM aspirante_documentos WHERE usuario_id = :uid AND tipo = :tipo");
                    $stmtCheck->execute([':uid' => $usuario_id, ':tipo' => $tipo_db]);
                    $existe = $stmtCheck->fetch();

                    if ($existe) {
                        $stmtUpd = $pdo->prepare("UPDATE aspirante_documentos SET archivo_ruta = :ruta, archivo_nombre = :nom WHERE id = :id");
                        $stmtUpd->execute([':ruta' => 'uploads/documentos/' . $nombre_unico, ':nom' => $archivo['name'], ':id' => $existe['id']]);
                    } else {
                        $stmtIns = $pdo->prepare("INSERT INTO aspirante_documentos (usuario_id, tipo, archivo_ruta, archivo_nombre) VALUES (:uid, :tipo, :ruta, :nom)");
                        $stmtIns->execute([':uid' => $usuario_id, ':tipo' => $tipo_db, ':ruta' => 'uploads/documentos/' . $nombre_unico, ':nom' => $archivo['name']]);
                    }
                    $mensaje = alerta_success('Documento ' . $tipo_db . ' subido exitosamente.');
                    registrar_log($pdo, 'Subir documento', 'aspirante_documentos', $usuario_id, "Tipo: $tipo_db");
                }
            }
        }

        $pdo->commit();
        registrar_log($pdo, 'Enviar postulación', 'usuarios', $usuario_id);
        $mensaje = alerta_success('Postulación enviada exitosamente. Su expediente está en revisión digital.');
        // Recargar datos
        $documentos->execute([':uid' => $usuario_id]);
        $documentos = $documentos->fetchAll();
        $baremo_respuestas->execute([':uid' => $usuario_id]);
        $baremo = $baremo_respuestas->fetchAll();
        $puntaje_baremo = 0;
        foreach ($baremo as $b) { if ($b['respuesta'] === 'si') $puntaje_baremo++; }
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje = alerta_error('Error al enviar postulación.');
    }
}

// Obtener preguntas del baremo
$preguntas = $pdo->query("SELECT * FROM baremo_preguntas ORDER BY categoria, orden")->fetchAll();
$preguntas_por_categoria = [];
foreach ($preguntas as $p) {
    $preguntas_por_categoria[$p['categoria']][] = $p;
}

$mensaje = $mensaje ?? '';
$titulo = 'Mi Postulación';
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
            <p>Aspirante: <?php echo h($nombre_asp); ?></p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active">📝 Mi Postulación</a>
            <a href="#estado" onclick="mostrarEstado()">🔍 Estado de Revisión</a>
            <a href="../../controlador/cerrar_sesion.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2>Mi Postulación a Postgrado</h2>
                    <p><?php echo fecha_hoy_formateada(); ?> &middot; Rol: Aspirante</p>
                </div>
                <div>
                    <!-- Semáforo de estado -->
                    <?php if ($estado === 'En Revision Digital'): ?>
                        <span class="badge badge-planar" style="font-size:1rem;padding:8px 16px;">🟡 EN REVISIÓN DIGITAL</span>
                    <?php elseif ($estado === 'Con Observaciones'): ?>
                        <span class="badge badge-planar" style="font-size:1rem;padding:8px 16px;background:#f39c12;">🔴 CON OBSERVACIONES</span>
                    <?php elseif ($estado === 'Admitido'): ?>
                        <span class="badge badge-inscripcion" style="font-size:1rem;padding:8px 16px;">🟢 ADMITIDO</span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <div class="baremo-resultado">
            <div class="baremo-score-total"><?php echo $puntaje_baremo; ?><small>/14</small></div>
            <div class="score-label">Puntaje del Baremo</div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <!-- BAREMO -->
            <div class="form-card">
                <div class="form-card-header">
                    <span class="form-step-badge">Baremo Digital</span>
                    <h3>📊 Cuestionario de Méritos Académicos</h3>
                </div>
                <div class="form-body">
                    <?php foreach ($preguntas_por_categoria as $categoria => $pregs): ?>
                    <h4 class="sub-section"><?php echo h($categoria); ?></h4>
                    <?php foreach ($pregs as $p): ?>
                    <?php
                        $resp_existente = null;
                        foreach ($baremo as $b) {
                            if ($b['id_pregunta'] == $p['id']) {
                                $resp_existente = $b['respuesta'];
                                break;
                            }
                        }
                    ?>
                    <div class="form-group">
                        <label><?php echo $p['orden']; ?>. <?php echo h($p['pregunta']); ?></label>
                        <div class="radio-row">
                            <label class="radio-opt">
                                <input type="radio" name="baremo[<?php echo $p['id']; ?>]" value="si"
                                    <?php echo $resp_existente === 'si' ? 'checked' : ''; ?>
                                    <?php echo $estado !== 'En Revision Digital' ? 'disabled' : ''; ?>> Sí
                            </label>
                            <label class="radio-opt">
                                <input type="radio" name="baremo[<?php echo $p['id']; ?>]" value="no"
                                    <?php echo $resp_existente === 'no' ? 'checked' : ''; ?>
                                    <?php echo $estado !== 'En Revision Digital' ? 'disabled' : ''; ?>> No
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- DOCUMENTOS -->
            <div class="form-card">
                <div class="form-card-header">
                    <span class="form-step-badge">Documentos</span>
                    <h3>📁 Carga de Requisitos Virtuales</h3>
                </div>
                <div class="form-body">
                    <div class="docs-instructions">
                        <strong>Instrucciones:</strong>
                        <ul>
                            <li>Formatos aceptados: JPG, PNG, PDF</li>
                            <li>Los documentos deben ser claros y legibles</li>
                            <li>Tamaño máximo: 5MB por archivo</li>
                        </ul>
                    </div>
                    <div class="baremo-docs" style="margin-top:15px;">
                        <div class="docs-title">Documentos requeridos</div>
                        <div class="docs-grid">
                            <?php
                            $tipos_docs = [
                                'Cedula' => ['label' => 'Documento de Identidad', 'input' => 'cedula'],
                                'Pasaporte' => ['label' => 'Pasaporte (si aplica)', 'input' => 'pasaporte'],
                                'Titulo' => ['label' => 'Título Universitario', 'input' => 'titulo'],
                                'Notas' => ['label' => 'Notas Certificadas', 'input' => 'notas'],
                                'Curriculum' => ['label' => 'Resumen Curricular', 'input' => 'curriculum'],
                            ];
                            foreach ($tipos_docs as $tipo => $cfg):
                                $doc_subido = false;
                                $doc_ruta = '';
                                foreach ($documentos as $d) {
                                    if ($d['tipo'] === $tipo) { $doc_subido = true; $doc_ruta = $d['archivo_ruta']; break; }
                                }
                            ?>
                            <div class="doc-item" style="margin-bottom:12px;padding:10px;background:#f9f9f9;border-radius:6px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                    <span class="doc-check"><?php echo $doc_subido ? '✅' : '⬜'; ?></span>
                                    <span><strong><?php echo h($cfg['label']); ?></strong></span>
                                    <?php if ($doc_subido): ?>
                                        <span style="font-size:0.75rem;color:#27ae60;">Subido</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="<?php echo $cfg['input']; ?>" accept=".jpg,.jpeg,.png,.pdf"
                                       style="font-size:0.8rem;width:100%;"
                                       <?php echo ($estado !== 'En Revision Digital' && $doc_subido) ? 'disabled' : ''; ?>>
                                <?php if ($doc_subido && $estado === 'Con Observaciones'): ?>
                                    <div style="font-size:0.7rem;color:#e67e22;margin-top:2px;">Corrige este documento según las observaciones</div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($estado === 'En Revision Digital'): ?>
            <div class="form-actions">
                <button type="submit" name="enviar_postulacion" class="btn-enviar"
                        onclick="return confirm('¿Está seguro de enviar su postulación digital? No podrá modificarla después.')">
                    📨 Enviar Postulación Digital
                </button>
            </div>
            <?php endif; ?>
        </form>
    </main>
</div>

<script>
function mostrarEstado() {
    alert('Estado actual: <?php echo $estado; ?>\n\n' +
          '🟡 EN REVISIÓN DIGITAL: La Secretaría está evaluando sus documentos.\n' +
          '🔴 CON OBSERVACIONES: Algunos documentos requieren corrección.\n' +
          '🟢 ADMITIDO: Ha sido admitido como Estudiante Regular.');
}
</script>

<?php require_once __DIR__ . '/../../includes/template_footer.php'; ?>
