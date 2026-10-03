<?php
/**
 * Página de Error 500 — Error interno del servidor
 * SIP-Postgrado UNEFA
 */

http_response_code(500);

// En producción, registrar con Logger si está disponible
if (class_exists('Logger')) {
    Logger::error('HTTP 500 mostrado al usuario', [
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
    ]);
}

// Respuesta JSON para peticiones API
$esApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($esApi) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Error interno del servidor', 'code' => 500]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Error interno | SIP-Postgrado UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        try {
            if (localStorage.getItem('sip_theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch(e) {}
    </script>
    <style>
        :root {
            --bg: #eef1f5;
            --card: rgba(255,255,255,0.9);
            --text: #1e1e2a;
            --muted: #7a7f8a;
            --blue: #001a57;
            --accent: #e74c3c;
        }
        [data-theme="dark"] {
            --bg: #0f1117;
            --card: rgba(22,27,46,0.92);
            --text: #e2e8f0;
            --muted: #94a3b8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--blue) url('../imagenes/FACHADA_AZULADA.webp') no-repeat center/cover fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 0;
        }
        .error-box {
            position: relative;
            z-index: 1;
            background: var(--card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 52px 48px;
            max-width: 540px;
            width: 90%;
            text-align: center;
            box-shadow: 0 24px 64px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.25);
            animation: slideIn 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .error-code {
            font-size: 7rem;
            font-weight: 800;
            color: var(--accent);
            line-height: 1;
            letter-spacing: -4px;
            text-shadow: 0 4px 20px rgba(231,76,60,0.3);
        }
        .error-icon { font-size: 3rem; margin: 12px 0; }
        h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }
        p {
            color: var(--muted);
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(231,76,60,0.35);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(231,76,60,0.5);
        }
        .btn-blue {
            background: linear-gradient(135deg, #1a6bc4, #001a57);
            box-shadow: 0 4px 16px rgba(26,107,196,0.35);
            margin-left: 10px;
        }
        .btn-blue:hover {
            box-shadow: 0 8px 24px rgba(26,107,196,0.5);
        }
        .logo-top {
            width: 48px;
            margin-bottom: 20px;
            opacity: 0.85;
        }
        .detail {
            background: rgba(231,76,60,0.08);
            border: 1px solid rgba(231,76,60,0.2);
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 0.72rem;
            color: var(--muted);
            margin-bottom: 28px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <img src="../imagenes/LOGO-1-1.png" alt="UNEFA" class="logo-top" onerror="this.style.display='none'">
        <div class="error-code">500</div>
        <div class="error-icon">⚙️</div>
        <h1>Error interno del servidor</h1>
        <p>Ocurrió un problema inesperado al procesar tu solicitud.<br>
           El equipo técnico ha sido notificado automáticamente.</p>
        <div class="detail">
            🕐 <?php echo date('d/m/Y H:i:s'); ?> · 
            Referencia: <strong><?php echo strtoupper(substr(md5(uniqid()), 0, 8)); ?></strong>
        </div>
        <a href="javascript:location.reload()" class="btn">
            🔄 Reintentar
        </a>
        <a href="../index.php" class="btn btn-blue">
            🏠 Inicio
        </a>
    </div>
</body>
</html>
