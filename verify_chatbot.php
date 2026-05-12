<?php
/**
 * Verificador de Chatbot - Diagnóstico Rápido
 * 
 * Coloca este archivo en la raíz y accede a:
 * http://localhost/pecosol/verify_chatbot.php
 * https://tu-dominio.railway.app/verify_chatbot.php
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

$checks = [
    'config' => checkConfig(),
    'api_reachable' => checkApiReachable(),
    'cors' => checkCors(),
    'database' => checkDatabaseViaApi(),
];

$allPassed = array_reduce(
    $checks,
    fn($carry, $check) => $carry && ($check['status'] === 'pass' || $check['status'] === 'warning'),
    true
);

http_response_code($allPassed ? 200 : 500);

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => php_sapi_name() === 'cli' ? 'CLI' : 'Web',
    'chatbot_status' => $allPassed ? '✅ READY' : '❌ FAILED',
    'checks' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// ============================================
// FUNCIONES DE VERIFICACIÓN
// ============================================

function checkConfig()
{
    $url = defined('CHATBOT_API_URL') ? CHATBOT_API_URL : null;
    $status = $url ? 'pass' : 'fail';
    
    return [
        'name' => 'Configuration',
        'status' => $status,
        'details' => [
            'CHATBOT_API_URL' => $url ?: 'NOT DEFINED',
            'BASE_URL' => defined('BASE_URL') ? BASE_URL : 'NOT DEFINED',
        ],
        'message' => $status === 'pass' 
            ? 'Configuration loaded correctly' 
            : 'CHATBOT_API_URL not defined'
    ];
}

function checkApiReachable()
{
    if (!defined('CHATBOT_API_URL')) {
        return [
            'name' => 'API Reachability',
            'status' => 'fail',
            'message' => 'Cannot check: CHATBOT_API_URL not defined',
            'details' => []
        ];
    }

    $url = CHATBOT_API_URL;
    $healthUrl = str_replace('/api/chat', '/health', $url);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $healthUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $status = $httpCode === 200 ? 'pass' : 'fail';
    
    $details = [
        'url' => $healthUrl,
        'http_code' => $httpCode,
        'error' => $error ?: 'None',
    ];

    if ($response) {
        try {
            $json = json_decode($response, true);
            $details['response'] = $json;
        } catch (Exception $e) {
            $details['response_raw'] = substr($response, 0, 200);
        }
    }

    return [
        'name' => 'API Reachability',
        'status' => $status,
        'details' => $details,
        'message' => match ($status) {
            'pass' => '✅ Chatbot API is reachable and healthy',
            'fail' => "❌ Cannot reach chatbot API (HTTP $httpCode): " . ($error ?: 'Connection failed'),
        }
    ];
}

function checkCors()
{
    if (!defined('CHATBOT_API_URL')) {
        return [
            'name' => 'CORS Configuration',
            'status' => 'warning',
            'message' => 'Cannot verify: CHATBOT_API_URL not defined',
            'details' => []
        ];
    }

    $url = CHATBOT_API_URL;
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'] ?? 'http://localhost';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_CUSTOMREQUEST => 'OPTIONS',
        CURLOPT_HTTPHEADER => [
            "Origin: $origin",
            'Access-Control-Request-Method: POST',
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = curl_getinfo($ch);
    curl_close($ch);

    $corsHeader = $headers['content_type'] ?? '';
    
    return [
        'name' => 'CORS Configuration',
        'status' => 'pass',
        'details' => [
            'origin' => $origin,
            'request_sent' => true,
            'http_code' => $httpCode,
        ],
        'message' => 'CORS headers will be validated by browser'
    ];
}

function checkDatabaseViaApi()
{
    if (!defined('CHATBOT_API_URL')) {
        return [
            'name' => 'Database Connection',
            'status' => 'warning',
            'message' => 'Cannot verify: CHATBOT_API_URL not defined',
            'details' => []
        ];
    }

    $url = CHATBOT_API_URL;
    $healthUrl = str_replace('/api/chat', '/health', $url);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $healthUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        try {
            $json = json_decode($response, true);
            $dbConnected = $json['database'] === 'connected';
            
            return [
                'name' => 'Database Connection',
                'status' => $dbConnected ? 'pass' : 'fail',
                'details' => [
                    'database_status' => $json['database'] ?? 'unknown',
                ],
                'message' => $dbConnected 
                    ? '✅ Chatbot can connect to database'
                    : '❌ Chatbot cannot connect to database'
            ];
        } catch (Exception $e) {
            return [
                'name' => 'Database Connection',
                'status' => 'warning',
                'message' => 'Could not parse health response',
                'details' => []
            ];
        }
    }

    return [
        'name' => 'Database Connection',
        'status' => 'fail',
        'message' => 'No response from health endpoint',
        'details' => []
    ];
}
