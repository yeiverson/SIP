<?php
/**
 * Página de Error 404 — Recurso no encontrado
 * SIP-Postgrado UNEFA
 */

http_response_code(404);

// Detectar si es una petición AJAX/API
$esApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($esApi) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Recurso no encontrado', 'code' => 404]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página no encontrada | SIP-Postgrado UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Anti-FOUC dark mode -->
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
            --accent: #1a6bc4;
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
            max-width: 520px;
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
            text-shadow: 0 4px 20px rgba(26,107,196,0.3);
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
            background: linear-gradient(135deg, #1a6bc4, #001a57);
            color: white;
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(26,107,196,0.35);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26,107,196,0.5);
        }
        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid currentColor;
            margin-left: 10px;
            box-shadow: none;
        }
        .btn-ghost:hover {
            background: rgba(122,127,138,0.1);
            box-shadow: none;
        }
        .logo-top {
            width: 48px;
            margin-bottom: 20px;
            opacity: 0.85;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <img src="../imagenes/LOGO-1-1.png" alt="UNEFA" class="logo-top" onerror="this.style.display='none'">
        <div class="error-code">404</div>
        <div class="error-icon">🔍</div>
        <h1>Página no encontrada</h1>
        <p>El recurso que buscas no existe o fue movido a otra ubicación.<br>
           Verifica la URL o regresa al panel principal.</p>
        <a href="<?php echo (isset($_SESSION) && !empty($_SESSION['usuario_id'])) ? '../dashboard.php' : '../index.php'; ?>" class="btn">
            🏠 Ir al inicio
        </a>
        <a href="javascript:history.back()" class="btn btn-ghost">← Volver</a>
    </div>
</body>
</html>
