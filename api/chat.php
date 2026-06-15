<?php
/**
 * /api/chat - Fallback endpoint para chatbot
 * 
 * Este archivo permite que el widget de chatbot tenga un endpoint válido
 * aunque el servidor Python de chatbot no esté disponible.
 * 
 * En entornos donde SÍ está disponible el servidor Python,
 * este archivo no se ejecutará (las rutas se redirigen correctamente).
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Origin');

// Manejar CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// If a real chatbot URL is configured and it's not this same endpoint,
// proxy the request to the external chatbot service.
$configuredChatbotUrl = defined('CHATBOT_API_URL') ? trim(CHATBOT_API_URL) : '';
$ownUrl = rtrim((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')) . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/api/chat'), '/\\'), '/') . '/api/chat';

if ($configuredChatbotUrl !== '' && strcasecmp(trim($configuredChatbotUrl, '/'), trim($ownUrl, '/')) !== 0) {
    $body = file_get_contents('php://input');
    if (function_exists('curl_version')) {
        $ch = curl_init($configuredChatbotUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($response !== false && $statusCode > 0) {
            $rawHeaders = substr($response, 0, $headerSize);
            $bodyResponse = substr($response, $headerSize);
            foreach (explode("\r\n", $rawHeaders) as $headerLine) {
                if (stripos($headerLine, 'Content-Type:') === 0 || stripos($headerLine, 'Access-Control-Allow-') === 0) {
                    header($headerLine);
                }
            }
            http_response_code($statusCode);
            echo $bodyResponse;
            exit;
        }
    }
}

// Chat endpoint (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'El servicio de chatbot no está disponible en este momento. Por favor intenta más tarde.'
    ]);
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
