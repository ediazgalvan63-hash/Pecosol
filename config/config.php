<?php
// config/config.php

// Session is already started in index.php before including this file
// No need to start session again here

/**
 * Función auxiliar para obtener valores de entorno de forma robusta.
 */
function env(string $name, $default = null)
{
    $value = getenv($name);
    if ($value === false && isset($_SERVER[$name])) {
        $value = $_SERVER[$name];
    }
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return $value;
}

/**
 * Configuración de zona horaria
 * Por defecto: Perú (UTC-5)
 * Local: se puede sobrescribir con variable de entorno APP_TIMEZONE
 */
$appTimezone = getenv('APP_TIMEZONE') ?: 'America/Lima';
date_default_timezone_set($appTimezone);
define('APP_TIMEZONE', $appTimezone);

/**
 * Zona horaria de la BD (cómo se guardan los DATETIME).
 * - En Railway normalmente el runtime/DB trabaja en UTC.
 * - En local suele guardarse en hora local.
 * Se puede forzar con variable de entorno DB_TIMEZONE.
 */
$isRailway = (bool) (
    getenv('RAILWAY_ENVIRONMENT') ?: getenv('RAILWAY_PROJECT_ID') ?: getenv('RAILWAY_SERVICE_ID') ?:
    (isset($_SERVER['HTTP_HOST']) && stripos($_SERVER['HTTP_HOST'], 'railway.app') !== false ? '1' : '')
);

// Si no se proporcionó DB_TIMEZONE, por seguridad asumimos UTC en Railway
$dbTimezone = getenv('DB_TIMEZONE') ?: ($isRailway ? 'UTC' : APP_TIMEZONE);
define('DB_TIMEZONE', $dbTimezone);

/**
 * URL base del proyecto.
 * - Local: usa APP_BASE_URL si existe, si no http://localhost/pecosol/
 * - Railway/producción: define APP_BASE_URL en variables de entorno
 */
function getCurrentRequestScheme(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $proto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
        if (in_array($proto, ['http', 'https'], true)) {
            return $proto;
        }
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
        return 'https';
    }

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return 'https';
    }

    if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443') {
        return 'https';
    }

    return 'http';
}

function getCurrentRequestHost(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
        if ($host !== '') {
            return $host;
        }
    }

    if (!empty($_SERVER['HTTP_HOST'])) {
        return $_SERVER['HTTP_HOST'];
    }

    if (!empty($_SERVER['SERVER_NAME'])) {
        return $_SERVER['SERVER_NAME'];
    }

    return 'localhost';
}

function isLocalRequest(): bool
{
    if (isset($_SERVER['HTTP_HOST']) && preg_match('#^(localhost|127\.0\.0\.1)(:\d+)?$#i', $_SERVER['HTTP_HOST'])) {
        return true;
    }
    if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true)) {
        return true;
    }
    return false;
}

/**
 * Session configuration
 * - Use a local writable folder for session storage when possible (tmp/sessions)
 * - Configure cookie params (secure, domain, samesite) based on request
 * - Start the session here so settings apply before any session data is used
 */

// Directorio local para sesiones (intentar usar en hosting como Railway)
$sessionDir = __DIR__ . '/../tmp/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
// Si el directorio es escribible, forzamos session.save_path
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    @ini_set('session.save_path', $sessionDir);
    @ini_set('session.gc_maxlifetime', '86400'); // 1 día
}

// Configurar parámetros de la cookie de sesión
$secure = (getCurrentRequestScheme() === 'https');
$cookieDomain = getCurrentRequestHost();
// Ajuste: si el host contiene puerto, removerlo en domain
if (strpos($cookieDomain, ':') !== false) {
    $cookieDomain = explode(':', $cookieDomain)[0];
}

session_name('PecosolSession');
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => $cookieDomain,
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
    }
    session_start();
}


/**
 * BASE_URL - Detección automática de URL base del proyecto
 * 
 * Prioridad:
 * 1. Variable de entorno APP_BASE_URL (si está configurada)
 * 2. CLI: usa default
 * 3. HTTP Request: detecta automáticamente
 */

$appBaseUrl = env('APP_BASE_URL', '');
$baseUrl = $appBaseUrl;

// Si no hay APP_BASE_URL configurada y no estamos en CLI
if (empty($baseUrl) && PHP_SAPI !== 'cli' && !empty($_SERVER['REQUEST_URI'])) {
    $scheme = getCurrentRequestScheme();
    $host = getCurrentRequestHost();
    
    // Detectar el path base del proyecto
    // En la mayoría de casos será / (local /pecosol o production root)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    if (!empty($scriptName) && $scriptName !== '/' && $scriptName !== '/index.php') {
        // SCRIPT_NAME es algo como /subdir/index.php o /pecosol/index.php
        $pathBase = rtrim(dirname($scriptName), '/\\');
    } else {
        // SCRIPT_NAME es / o /index.php - proyecto en raíz
        $pathBase = '';
    }
    
    $baseUrl = $scheme . '://' . $host . ($pathBase ? $pathBase : '') . '/';
}

// Fallback a default
$baseUrl = $baseUrl ?: 'http://localhost/pecosol/';

// Asegurar trailing slash
$baseUrl = rtrim($baseUrl, '/') . '/';
define('BASE_URL', $baseUrl);

// Heurística: si estamos en Railway y no hay APP_BASE_URL explícita, forzar la URL base
try {
    $hostLower = strtolower($_SERVER['HTTP_HOST'] ?? '');
    if (empty($appBaseUrl) && strpos($hostLower, 'railway.app') !== false) {
        $scheme = getCurrentRequestScheme();
        $forced = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? $hostLower) . '/';
        // Re-define BASE_URL si detectamos dominio railway
        define('BASE_URL', $forced);
        $baseUrl = $forced;
    }
} catch (Exception $e) {
    // noop
}


/**
 * URL del API del chatbot (FastAPI).
 * - Local: http://127.0.0.1:8000/api/chat
 * - Railway: define CHATBOT_API_URL con tu dominio de servicio Python
 * - En producción sin CHATBOT_API_URL no se asume un endpoint remoto
 */
$chatbotApiUrl = env('CHATBOT_API_URL', '') ?: env('RAILWAY_SERVICE_PECOSOL_CHATBOT_URL', '');
$chatbotApiUrl = trim($chatbotApiUrl);

if ($chatbotApiUrl !== '') {
    if (!preg_match('#^https?://#i', $chatbotApiUrl)) {
        $chatbotApiUrl = 'https://' . preg_replace('#^https?://#i', '', $chatbotApiUrl);
    }
    $chatbotApiUrl = rtrim($chatbotApiUrl, '/');
    if (!preg_match('#/api/chat$#i', $chatbotApiUrl)) {
        $chatbotApiUrl .= '/api/chat';
    }
} else {
    if (isLocalRequest()) {
        $chatbotApiUrl = 'http://127.0.0.1:8000/api/chat';
    } else {
        $chatbotApiUrl = rtrim(BASE_URL, '/') . '/api/chat';
    }
}
define('CHATBOT_API_URL', $chatbotApiUrl);

/**
 * Intentar iniciar automáticamente el servidor Python del chatbot
 * cuando la app está en un entorno local de Windows.
 * 
 * OPTIMIZACIÓN: Timeout muy corto (0.2s) para no bloquear el login
 */
function isChatbotServerRunning(): bool
{
    $host = '127.0.0.1';
    $port = 8000;
    $timeout = 0.2; // ⚡ Reducido de 1s a 0.2s para no bloquear
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($socket) {
        fclose($socket);
        return true;
    }
    return false;
}

function getPythonVersion(string $pythonExe): ?array
{
    $output = @shell_exec(escapeshellarg($pythonExe) . ' --version 2>&1');
    if (!is_string($output)) {
        return null;
    }

    if (!preg_match('/Python\s+(\d+)\.(\d+)\.(\d+)/i', trim($output), $matches)) {
        return null;
    }

    return [
        'major' => (int) $matches[1],
        'minor' => (int) $matches[2],
        'patch' => (int) $matches[3],
    ];
}

function findCompatiblePython(): ?string
{
    $rootDir = dirname(__DIR__);
    $candidates = [
        $rootDir . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe',
        'py -3.13',
        'py -3.12',
        'python3.13',
        'python3.12',
        'python',
    ];

    foreach ($candidates as $cmd) {
        $version = getPythonVersion($cmd);
        if (!$version) {
            continue;
        }

        if ($version['major'] === 3 && $version['minor'] <= 14) {
            return $cmd;
        }
    }

    return null;
}

function startLocalChatbotServer(): void
{
    $rootDir = dirname(__DIR__);
    $pythonApiDir = $rootDir . DIRECTORY_SEPARATOR . 'python_api';
    $mainScript = $pythonApiDir . DIRECTORY_SEPARATOR . 'main.py';
    $vbsScript = $pythonApiDir . DIRECTORY_SEPARATOR . 'AutoStart-Chatbot.vbs';

    if (!file_exists($mainScript)) {
        return;
    }

    if (file_exists($vbsScript)) {
        $command = 'wscript.exe ' . escapeshellarg($vbsScript);
    } else {
        $pythonExe = findCompatiblePython();
        if (!$pythonExe) {
            return;
        }
        $command = 'cd /d ' . escapeshellarg($pythonApiDir) . ' && start "" /B ' . escapeshellarg($pythonExe) . ' -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload >nul 2>&1';
    }

    try {
        if (class_exists('COM')) {
            $shell = new COM('WScript.Shell');
            // Ejecutar en background sin esperar (Async = true, WindowStyle = 0 = hidden)
            $shell->Run($command, 0, false);
        } else {
            // Fallback si COM no disponible
            if (function_exists('popen')) {
                @pclose(@popen('cmd /c ' . $command, 'r'));
            } elseif (function_exists('exec')) {
                @exec($command . ' &');
            }
        }
    } catch (Exception $e) {
        // Si COM falla, intentar igualmente con popen/exec
        if (function_exists('popen')) {
            @pclose(@popen('cmd /c ' . $command, 'r'));
        } elseif (function_exists('exec')) {
            @exec($command . ' &');
        }
    }
}

// ⚡ OPTIMIZACIÓN MEJORADA: Iniciar chatbot automáticamente SIN BLOQUEAR
// Solo intenta una vez por sesión (usa $_SESSION para evitar reintentos)
// La función startLocalChatbotServer() es NOW NON-BLOCKING, no espera a que responda
if (!isset($_SESSION['chatbot_startup_attempted'])) {
    $_SESSION['chatbot_startup_attempted'] = true;
    
    if (PHP_OS_FAMILY === 'Windows' && preg_match('#^https?://(127\.0\.0\.1|localhost)(:\d+)?/api/chat#i', CHATBOT_API_URL)) {
        // Check rápido: ¿ya está corriendo?
        if (!isChatbotServerRunning()) {
            // Iniciar en background (NO BLOQUEA)
            startLocalChatbotServer();
        }
    }
}

// Nombre del proyecto
define('PROJECT_NAME', 'Pecosol');

/**
 * Función auxiliar para formatear fechas con zona horaria
 * @param string $dateString Fecha en formato string (e.g., '2026-05-04 21:20:30')
 * @param string $format Formato de salida (default: 'd-m-Y H:i')
 * @return string Fecha formateada en la zona horaria local
 */
function formatSaleDate($dateString, $format = 'd-m-Y H:i') {
    if (empty($dateString)) {
        return '';
    }
    try {
        $localTz = new DateTimeZone(APP_TIMEZONE);

        // MySQL DATETIME no incluye zona horaria (se interpreta como "hora local del negocio").
        // Solo convertimos si la cadena ya trae un offset o sufijo Z (UTC).
        $hasExplicitTz = (bool) preg_match('/(Z|[+\-]\d{2}:\d{2})$/i', trim((string) $dateString));

        if ($hasExplicitTz) {
            $dt = new DateTime($dateString);
            $dt->setTimeZone($localTz);
            return $dt->format($format);
        }

        // Si viene sin tz, interpretar según DB_TIMEZONE y convertir a hora local.
        $sourceTz = new DateTimeZone(defined('DB_TIMEZONE') ? DB_TIMEZONE : APP_TIMEZONE);
        $dt = new DateTime($dateString, $sourceTz);
        $dt->setTimeZone($localTz);
        return $dt->format($format);
    } catch (Exception $e) {
        // Fallback: usar la función date() estándar
        return date($format, strtotime($dateString));
    }
}
