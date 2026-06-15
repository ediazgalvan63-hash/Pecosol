<?php
/**
 * Diagnóstico Interactivo del Chatbot
 * 
 * Muestra el estado actual del chatbot en local vs Railway
 * y qué falta configurar.
 */

require_once __DIR__ . '/../config/config.php';

$isRailway = strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false;
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']);

$environment = $isRailway ? 'Railway' : ($isLocal ? 'Local' : 'Other');
$chatbotUrl = defined('CHATBOT_API_URL') ? CHATBOT_API_URL : null;

// Intentar conectar al chatbot
$chatbotHealthy = false;
$chatbotError = null;

if ($chatbotUrl) {
    $healthUrl = str_replace('/api/chat', '/health', $chatbotUrl);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $healthUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_CONNECTTIMEOUT => 2,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $chatbotHealthy = $httpCode === 200;
    if (!$chatbotHealthy) {
        $chatbotError = $error ?: "HTTP $httpCode";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤖 Diagnóstico Chatbot - Pecosol</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #eee;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #00d4ff;
            font-size: 2.5em;
        }
        
        .card {
            background: #0f3460;
            border: 1px solid #00d4ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #00d4ff;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: rgba(0, 212, 255, 0.05);
            margin-bottom: 8px;
            border-left: 4px solid #00d4ff;
            border-radius: 4px;
        }
        
        .status-label {
            font-weight: 600;
            color: #00d4ff;
            min-width: 150px;
        }
        
        .status-value {
            flex: 1;
            text-align: right;
            word-break: break-all;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .badge-success {
            background: #00d900;
            color: #000;
        }
        
        .badge-warning {
            background: #ff9800;
            color: #000;
        }
        
        .badge-error {
            background: #ff4444;
            color: #fff;
        }
        
        .action-box {
            background: #1a4d7a;
            border: 2px solid #00d4ff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .action-box h3 {
            color: #00d4ff;
            margin-bottom: 10px;
        }
        
        .action-box p {
            margin: 8px 0;
            line-height: 1.6;
        }
        
        .code-block {
            background: #0a0e27;
            border: 1px solid #00d4ff;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            margin: 10px 0;
            overflow-x: auto;
        }
        
        .warning-box {
            background: #664400;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .success-box {
            background: #004400;
            border-left: 4px solid #00d900;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .error-box {
            background: #440000;
            border-left: 4px solid #ff4444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #00d4ff;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 10px;
            border: none;
            cursor: pointer;
        }
        
        .button:hover {
            background: #00a8cc;
        }
        
        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .comparison-box {
            background: rgba(0, 212, 255, 0.05);
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #00d4ff;
        }
        
        .comparison-box h4 {
            color: #00d4ff;
            margin-bottom: 10px;
        }
        
        .comparison-box p {
            font-size: 0.9em;
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .comparison {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Diagnóstico del Chatbot</h1>
        
        <!-- Estado Actual -->
        <div class="card">
            <h2>📊 Estado Actual</h2>
            
            <div class="status-row">
                <span class="status-label">Entorno:</span>
                <span class="status-value">
                    <?php echo $environment; ?>
                    <span class="status-badge badge-<?php echo $isLocal ? 'success' : ($isRailway ? 'warning' : 'error'); ?>">
                        <?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?>
                    </span>
                </span>
            </div>
            
            <div class="status-row">
                <span class="status-label">CHATBOT_API_URL:</span>
                <span class="status-value">
                    <?php if ($chatbotUrl): ?>
                        <code><?php echo htmlspecialchars($chatbotUrl); ?></code>
                        <span class="status-badge <?php echo $chatbotHealthy ? 'badge-success' : 'badge-error'; ?>">
                            <?php echo $chatbotHealthy ? '✅ Accesible' : '❌ No accesible'; ?>
                        </span>
                    <?php else: ?>
                        <strong style="color: #ff4444;">NO DEFINIDA</strong>
                        <span class="status-badge badge-error">❌ Error</span>
                    <?php endif; ?>
                </span>
            </div>
            
            <?php if (!$chatbotUrl && $isRailway): ?>
            <div class="error-box">
                <strong>❌ PROBLEMA IDENTIFICADO</strong><br>
                La variable <code>CHATBOT_API_URL</code> no está configurada en Railway.
            </div>
            <?php elseif (!$chatbotHealthy && $chatbotUrl): ?>
            <div class="error-box">
                <strong>⚠️ PROBLEMA DETECTADO</strong><br>
                El servicio chatbot no es accesible en: <code><?php echo htmlspecialchars($chatbotUrl); ?></code>
                <br>Error: <code><?php echo htmlspecialchars($chatbotError); ?></code>
            </div>
            <?php elseif ($chatbotHealthy): ?>
            <div class="success-box">
                <strong>✅ TODO CORRECTO</strong><br>
                El chatbot está configurado y accesible. ¡Ya debería funcionar!
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Comparación Local vs Railway -->
        <div class="card">
            <h2>📈 Comparación: Local vs Railway</h2>
            
            <div class="comparison">
                <div class="comparison-box">
                    <h4>💻 Local (Funciona ✅)</h4>
                    <p><strong>URL del chatbot:</strong><br>
                    <code>http://127.0.0.1:8000/api/chat</code></p>
                    <p><strong>Variable:</strong><br>
                    Automática (detecta localhost)</p>
                    <p><strong>BD:</strong><br>
                    localhost</p>
                </div>
                
                <div class="comparison-box">
                    <h4>🚂 Railway (Necesita Configuración)</h4>
                    <p><strong>URL del chatbot:</strong><br>
                    <code>https://pecosol-chatbot.railway.app/api/chat</code></p>
                    <p><strong>Variable:</strong><br>
                    <code>CHATBOT_API_URL</code> (explícita)</p>
                    <p><strong>BD:</strong><br>
                    mysql.railway.internal</p>
                </div>
            </div>
        </div>
        
        <!-- Instrucciones de Configuración -->
        <?php if (!$chatbotUrl && $isRailway): ?>
        <div class="card action-box">
            <h3>🚀 Solución: Configurar en Railway (5 minutos)</h3>
            
            <p><strong>Paso 1:</strong> Abre <a href="https://railway.app" target="_blank" style="color: #00d4ff;">https://railway.app</a></p>
            
            <p><strong>Paso 2:</strong> Ve a tu proyecto Pecosol → Servicio <code>pecosol-web</code></p>
            
            <p><strong>Paso 3:</strong> Haz clic en la pestaña <code>Variables</code> (Environment)</p>
            
            <p><strong>Paso 4:</strong> Crea o edita esta variable:</p>
            <div class="code-block">
Nombre:  CHATBOT_API_URL
Valor:   https://pecosol-chatbot.railway.app/api/chat
            </div>
            
            <p><em>⚠️ Reemplaza <code>pecosol-chatbot</code> con el nombre EXACTO de tu servicio chatbot en Railway</em></p>
            
            <p><strong>Paso 5:</strong> Haz clic en "Save" y espera el Deploy (1-2 minutos)</p>
            
            <p><strong>Paso 6:</strong> Actualiza esta página para verificar</p>
            
            <button class="button" onclick="location.reload()">🔄 Actualizar Diagnóstico</button>
        </div>
        <?php elseif (!$chatbotHealthy && $chatbotUrl): ?>
        <div class="card action-box">
            <h3>⚙️ El Servicio Chatbot No Está Disponible</h3>
            
            <p>La variable está configurada pero el servicio no responde.</p>
            
            <p><strong>Causas posibles:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>El servicio <code>pecosol-chatbot</code> está roto o crasheando</li>
                <li>Falta alguna variable de entorno en el servicio chatbot</li>
                <li>La base de datos no está accesible desde el servicio chatbot</li>
                <li>OpenAI API key es inválida</li>
            </ul>
            
            <p><strong>Solución:</strong></p>
            <p>1. Abre Railway y ve a servicio <code>pecosol-chatbot</code></p>
            <p>2. Revisa la pestaña <code>Logs</code> para ver el error</p>
            <p>3. Verifica que tenga estas variables configuradas:</p>
            <div class="code-block">
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=(tu password)
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
API_HOST=0.0.0.0
API_PORT=8000
CHATBOT_ALLOWED_ORIGINS=https://tu-dominio-web.railway.app
            </div>
        </div>
        <?php elseif ($chatbotHealthy): ?>
        <div class="card action-box">
            <h3>✅ El Chatbot Está Correctamente Configurado</h3>
            
            <p>Las variables están bien y el servicio está disponible.</p>
            
            <p><strong>Próximos pasos:</strong></p>
            <ol style="margin-left: 20px; margin-top: 10px;">
                <li>Abre tu web en Railway: <a href="<?php echo defined('BASE_URL') ? htmlspecialchars(BASE_URL) : '#'; ?>" target="_blank" style="color: #00d4ff;">Ver sitio</a></li>
                <li>Haz clic en el botón 🤖 (abajo a la derecha)</li>
                <li>Haz una pregunta: "¿Cuántos productos tenemos?"</li>
                <li>El chatbot debe responder con datos reales</li>
            </ol>
            
            <?php if (defined('BASE_URL')): ?>
            <a href="<?php echo htmlspecialchars(BASE_URL); ?>" class="button" target="_blank">🌐 Ir a mi sitio</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Información Técnica -->
        <div class="card">
            <h2>🔧 Información Técnica</h2>
            
            <div class="status-row">
                <span class="status-label">Base URL:</span>
                <span class="status-value">
                    <code><?php echo defined('BASE_URL') ? htmlspecialchars(BASE_URL) : 'N/A'; ?></code>
                </span>
            </div>
            
            <div class="status-row">
                <span class="status-label">PHP SAPI:</span>
                <span class="status-value">
                    <?php echo php_sapi_name(); ?>
                </span>
            </div>
            
            <div class="status-row">
                <span class="status-label">PHP Version:</span>
                <span class="status-value">
                    <?php echo PHP_VERSION; ?>
                </span>
            </div>
        </div>
        
        <!-- Notas -->
        <div style="text-align: center; margin-top: 40px; color: #888;">
            <p>Para más información, revisa: <a href="<?php echo defined('BASE_URL') ? htmlspecialchars(BASE_URL . 'CHATBOT_RAILWAY_DIAGNOSIS.md') : '#'; ?>" style="color: #00d4ff;">CHATBOT_RAILWAY_DIAGNOSIS.md</a></p>
        </div>
    </div>
</body>
</html>
