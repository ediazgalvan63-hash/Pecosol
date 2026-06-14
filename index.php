<?php
// index.php

// 0) CRÍTICO: Detectar rutas especiales ANTES de cualquier cosa
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = rtrim($requestPath, '/');

// Si es /health o /api/chat, responder directamente como JSON
if ($requestPath === '/health' || $requestPath === '/api/chat') {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/api/chat.php';
    exit;
}

// 1) Iniciar sesión (después de las rutas especiales)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bypass de servidor: servir directamente assets estáticos si existen
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = rtrim($requestPath, '/');

// Rutas estáticas que Apache debería servir directamente
$staticPrefixes = ['/assets/', '/favicon.ico', '/robots.txt', '/sitemap.xml'];
$isStaticRequest = false;

foreach ($staticPrefixes as $prefix) {
    if ($requestPath === $prefix || str_starts_with($requestPath, $prefix)) {
        $isStaticRequest = true;
        break;
    }
}

if ($isStaticRequest) {
    $filePath = realpath(__DIR__ . $requestPath);
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
