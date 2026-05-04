<?php
// index.php

// 1) Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Incluir el autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

// 3) Incluir configuraciones y conexión a BD
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// BYPASS: Lee desde variable de entorno BYPASS_PASSWORD_VERIFICATION
// Puedes establecerla en Railway sin necesidad de redeployar:
// BYPASS_PASSWORD_VERIFICATION=true
if (!defined('BYPASS_PASSWORD_VERIFICATION')) {
    $bypass = getenv('BYPASS_PASSWORD_VERIFICATION');
    define('BYPASS_PASSWORD_VERIFICATION', $bypass === 'true' || $bypass === '1');
}

// 4) Determinar controlador y acción desde la URL
$controllerParam = $_GET['controller'] ?? 'auth';
$actionParam     = $_GET['action']     ?? 'login';

// Sanitizar valores para evitar rutas no válidas o manipuladas
$controllerParam = preg_replace('/[^a-zA-Z0-9_]/', '', $controllerParam) ?: 'auth';
$actionParam     = preg_replace('/[^a-zA-Z0-9_]/', '', $actionParam)     ?: 'login';

// Normalizar nombres de controlador y acción
$controllerName = ucfirst(strtolower($controllerParam)) . 'Controller';
$actionName     = $actionParam;

// 5) Validar controlador conocido antes de incluirlo
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
