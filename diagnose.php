<?php
/**
 * Diagnóstico de configuración en Railway
 * Acceder desde: https://tu-dominio.railway.app/diagnose.php
 */

session_start();
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');

$diagnostics = [];

// 1. URLs detectadas
$diagnostics['URLs'] = [
    'BASE_URL' => BASE_URL,
    'CHATBOT_API_URL' => CHATBOT_API_URL,
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'N/A',
    'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'N/A',
    'HTTPS' => $_SERVER['HTTPS'] ?? 'N/A',
    'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A',
    'HTTP_X_FORWARDED_HOST' => $_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'N/A',
];

// 2. Archivos estáticos
$diagnostics['Assets'] = [
    'style.css exists' => is_file(__DIR__ . '/assets/css/style.css') ? 'YES' : 'NO',
    'style.css readable' => is_readable(__DIR__ . '/assets/css/style.css') ? 'YES' : 'NO',
    'LogoPecosol.png exists' => is_file(__DIR__ . '/assets/img/LogoPecosol.png') ? 'YES' : 'NO',
    'LogoPecosol.png readable' => is_readable(__DIR__ . '/assets/img/LogoPecosol.png') ? 'YES' : 'NO',
];

// 3. Base de datos
$diagnostics['Database'] = [
    'Connection type' => 'Check below...',
];

try {
    if (isset($conn) && $conn) {
        $result = $conn->query('SELECT 1');
        $diagnostics['Database']['Status'] = 'Connected ✓';
        $diagnostics['Database']['Server'] = $conn->server_info ?? 'N/A';
    } else {
        $diagnostics['Database']['Status'] = 'Not connected ✗';
    }
} catch (Exception $e) {
    $diagnostics['Database']['Status'] = 'Error: ' . $e->getMessage();
}

// 4. Variables de entorno
$diagnostics['Environment Variables'] = [
    'APP_BASE_URL' => env('APP_BASE_URL', 'NOT SET'),
    'CHATBOT_API_URL' => env('CHATBOT_API_URL', 'NOT SET'),
    'RAILWAY_SERVICE_PECOSOL_CHATBOT_URL' => env('RAILWAY_SERVICE_PECOSOL_CHATBOT_URL', 'NOT SET'),
    'DB_HOST' => env('DB_HOST', 'NOT SET'),
    'DB_USER' => env('DB_USER', 'NOT SET'),
    'PORT' => env('PORT', 'NOT SET'),
    'ENVIRONMENT' => env('ENVIRONMENT', 'NOT SET'),
];

// 5. PHP y servidor
$diagnostics['System'] = [
    'PHP Version' => phpversion(),
    'Server API' => php_sapi_name(),
    'OS' => php_uname(),
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'Working Directory' => getcwd(),
];

// 6. Test de assets
$diagnostics['Asset Loading Test'] = [];
$assetTests = [
    '/assets/css/style.css' => 'CSS Stylesheet',
    '/assets/img/LogoPecosol.png' => 'Logo Image',
    '/index.php' => 'Main App',
];

foreach ($assetTests as $path => $description) {
    $url = (function_exists('BASE_URL') ? rtrim(BASE_URL, '/') : '') . $path;
    $diagnostics['Asset Loading Test'][$description . ' (' . $path . ')'] = $url;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Pecosol</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #0a0e27;
            color: #00d4ff;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 0 10px #00d4ff;
            border-bottom: 2px solid #00d4ff;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 30px;
            border: 1px solid #00d4ff;
            border-radius: 5px;
            overflow: hidden;
            background: rgba(0, 212, 255, 0.05);
        }
        .section-title {
            background: linear-gradient(90deg, #00d4ff, #0099cc);
            padding: 15px;
            font-weight: bold;
            font-size: 18px;
            color: #0a0e27;
        }
        .section-content {
            padding: 15px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #00d4ff;
            gap: 20px;
        }
        .row:last-child {
            border-bottom: none;
        }
        .row-label {
            font-weight: bold;
            min-width: 250px;
            color: #00ffff;
        }
        .row-value {
            flex: 1;
            word-break: break-all;
            color: #00d4ff;
        }
        .status-ok {
            color: #00ff00;
        }
        .status-error {
            color: #ff0000;
        }
        .status-warning {
            color: #ffff00;
        }
        footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #00d4ff;
            color: #0099cc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Configuración - Pecosol</h1>

        <?php foreach ($diagnostics as $sectionName => $data): ?>
        <div class="section">
            <div class="section-title"><?php echo htmlspecialchars($sectionName); ?></div>
            <div class="section-content">
                <?php foreach ($data as $key => $value): ?>
                    <?php
                    $statusClass = '';
                    if (strpos($value, 'YES') !== false || strpos($value, 'Connected') !== false) {
                        $statusClass = 'status-ok';
                    } elseif (strpos($value, 'NO') !== false || strpos($value, 'Error') !== false) {
                        $statusClass = 'status-error';
                    } elseif (strpos($value, 'NOT SET') !== false) {
                        $statusClass = 'status-warning';
                    }
                    ?>
                    <div class="row">
                        <div class="row-label"><?php echo htmlspecialchars($key); ?></div>
                        <div class="row-value <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <footer>
            <p>Este archivo es solo para diagnóstico. Elimina después de revisar.</p>
        </footer>
    </div>
</body>
</html>
