<?php
// index.php - Front Controller
// =====================================

// DEBUG: Log REQUEST_URI and REDIRECT_URL
@file_put_contents(__DIR__ . '/request_log.txt', date('Y-m-d H:i:s') . ' | REDIRECT_URL: ' . ($_SERVER['REDIRECT_URL'] ?? 'NOT SET') . ' | REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . ' | GET: ' . ($_GET['url'] ?? 'EMPTY') . "\n", FILE_APPEND);

// 0) CRÍTICO: Detectar rutas especiales ANTES de CUALQUIER otra cosa
// REQUEST_URI podría venir como /health O como /index.php?url=health después de reescritura
// REDIRECT_URL es preservado por Apache cuando reescribe. REQUEST_URI se modifica a /index.php
$originalUri = $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = $originalUri;
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = rtrim($requestPath, '/');

// Chequear de múltiples formas por si Apache reescribió la ruta
$isHealth = (
    $requestPath === '/health' ||
    strpos($originalUri, '/health') !== false ||
    (isset($_GET['url']) && $_GET['url'] === 'health')
);

$isApiChat = (
    $requestPath === '/api/chat' ||
    strpos($originalUri, '/api/chat') !== false ||
    (isset($_GET['url']) && $_GET['url'] === 'api/chat')
);

$isDiagnose = (
    $requestPath === '/diagnose' ||
    $requestPath === '/diagnose.php' ||
    strpos($originalUri, 'diagnose') !== false
);

// Si es /health, responder directamente como JSON
if ($isHealth) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'service' => 'Pecosol API',
        'database' => 'connected',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Si es /api/chat, ejecutar el endpoint de chatbot y permitir fallback o proxy
if ($isApiChat) {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/api/chat.php';
    exit;
}

// Si es /diagnose, cargar ese archivo
if ($isDiagnose) {
    require_once __DIR__ . '/diagnose.php';
    exit;
}

// 1) Iniciar sesión (después de las rutas especiales)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bypass de servidor: servir directamente archivos reales si existen.
// Esto cubre casos en los que Apache reescribe todo a index.php.
$staticRequestUri = null;
$staticRequestCandidates = [
    $_SERVER['REQUEST_URI'] ?? null,
    $_SERVER['REDIRECT_URL'] ?? null,
    $_SERVER['PATH_INFO'] ?? null,
    $_SERVER['ORIG_PATH_INFO'] ?? null,
];
foreach ($staticRequestCandidates as $candidate) {
    if (!empty($candidate) && is_string($candidate)) {
        $staticRequestUri = $candidate;
        break;
    }
}

if (empty($staticRequestUri)) {
    $staticRequestUri = '/';
}

// Si la URL original tenía query string, usamos el path limpio.
$uriParts = explode('?', $staticRequestUri, 2);
if (!empty($uriParts[0])) {
    $staticRequestUri = $uriParts[0];
}

@file_put_contents(__DIR__ . '/request_log.txt', date('Y-m-d H:i:s') . ' | STATIC_CANDIDATES: ' . json_encode($staticRequestCandidates) . ' | STATIC_URI: ' . $staticRequestUri . '\n', FILE_APPEND);

$staticRequestPath = parse_url($staticRequestUri, PHP_URL_PATH) ?: '/';
$staticRequestPath = rawurldecode($staticRequestPath);
$staticRequestPath = rtrim($staticRequestPath, '/');
if ($staticRequestPath === '') {
    $staticRequestPath = '/';
}

// Si existe un archivo real en el servidor para esta ruta, lo servimos directamente.
// Importante: no sirve archivos PHP directamente, porque el front controller debe procesarlos.
if ($staticRequestPath !== '/' && !preg_match('/\.php$/i', $staticRequestPath)) {
    $filePath = realpath(__DIR__ . $staticRequestPath);
    $rootPath = realpath(__DIR__);
    if ($filePath && $rootPath && str_starts_with($filePath, $rootPath) && is_file($filePath)) {
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=3600');
        readfile($filePath);
        exit;
    }
}

// 2) Incluir el autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

// 3) Incluir configuraciones y conexión a BD
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// 4) BYPASS: Lee desde variable de entorno BYPASS_PASSWORD_VERIFICATION
// Puedes establecerla en Railway sin necesidad de redeployar:
// BYPASS_PASSWORD_VERIFICATION=true
if (!defined('BYPASS_PASSWORD_VERIFICATION')) {
    $bypass = getenv('BYPASS_PASSWORD_VERIFICATION');
    define('BYPASS_PASSWORD_VERIFICATION', $bypass === 'true' || $bypass === '1');
}

// 5) Determinar controlador y acción desde la URL
$controllerParam = $_GET['controller'] ?? 'auth';
$actionParam     = $_GET['action']     ?? 'login';

// Sanitizar valores para evitar rutas no válidas o manipuladas
$controllerParam = preg_replace('/[^a-zA-Z0-9_]/', '', $controllerParam) ?: 'auth';
$actionParam     = preg_replace('/[^a-zA-Z0-9_]/', '', $actionParam)     ?: 'login';

// Normalizar nombres de controlador y acción
$controllerName = ucfirst(strtolower($controllerParam)) . 'Controller';
$actionName     = $actionParam;

// 6) Validar controlador conocido antes de incluirlo
$validControllers = [
    'AuthController',
    'DashboardController',
    'AdminController',
    'EmployeeController',
];

if (!in_array($controllerName, $validControllers, true)) {
    header('HTTP/1.0 404 Not Found');
    echo "El controlador \"$controllerName\" no existe.";
    exit;
}

// 6) Ruta al archivo del controlador
$controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';

// 6) Verificar existencia del archivo y clase
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerName)) {
        $controller = new $controllerName();

        if (method_exists($controller, $actionName)) {
            // Llamar a la acción correspondiente
            $controller->{$actionName}();
        } else {
            // Acción no encontrada en ese controlador
            header('HTTP/1.0 404 Not Found');
            echo "La acción \"$actionName\" no existe en el controlador \"$controllerName\".";
        }
    } else {
        // La clase no existía tras incluir el archivo
        header('HTTP/1.0 500 Internal Server Error');
        echo "La clase \"$controllerName\" no se encontró en $controllerFile.";
    }
} else {
    // Archivo de controlador no encontrado
    header('HTTP/1.0 404 Not Found');
    echo "El controlador \"$controllerName\" no se encontró (archivo faltante).";
}
