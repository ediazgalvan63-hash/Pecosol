<?php
// config/config.php

// Iniciar sesión para guardar estado del chatbot (solo si no está iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
$isRailway = (bool) (getenv('RAILWAY_ENVIRONMENT') ?: getenv('RAILWAY_PROJECT_ID') ?: getenv('RAILWAY_SERVICE_ID'));
$dbTimezone = getenv('DB_TIMEZONE') ?: ($isRailway ? 'UTC' : APP_TIMEZONE);
define('DB_TIMEZONE', $dbTimezone);

/**
 * URL base del proyecto.
 * - Local: usa APP_BASE_URL si existe, si no http://localhost/pecosol/
 * - Railway/producción: define APP_BASE_URL en variables de entorno
 */
$baseUrl = getenv('APP_BASE_URL') ?: 'http://localhost/pecosol/';
$baseUrl = rtrim($baseUrl, '/') . '/';
define('BASE_URL', $baseUrl);

/**
 * URL del API del chatbot (FastAPI).
 * - Local: http://127.0.0.1:8000/api/chat
 * - Railway: define CHATBOT_API_URL con tu dominio de servicio Python
 * - Si no existe CHATBOT_API_URL, usa APP_BASE_URL + /api/chat
 */
$chatbotApiUrl = getenv('CHATBOT_API_URL') ?: getenv('RAILWAY_SERVICE_PECOSOL_CHATBOT_URL');
if ($chatbotApiUrl) {
    $chatbotApiUrl = trim($chatbotApiUrl);
    if (!preg_match('#^https?://#i', $chatbotApiUrl)) {
        $chatbotApiUrl = 'https://' . preg_replace('#^https?://#i', '', $chatbotApiUrl);
    }
    $chatbotApiUrl = rtrim($chatbotApiUrl, '/');
    if (!preg_match('#/api/chat$#i', $chatbotApiUrl)) {
        $chatbotApiUrl .= '/api/chat';
    }
} else {
    $appBaseUrl = getenv('APP_BASE_URL');
    if ($appBaseUrl && !preg_match('#^(https?://)?(localhost|127\.0\.0\.1)#i', $appBaseUrl)) {
        $chatbotApiUrl = rtrim($appBaseUrl, '/') . '/api/chat';
    } else {
        $chatbotApiUrl = 'http://127.0.0.1:8000/api/chat';
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

        if ($version['major'] === 3 && $version['minor'] <= 13) {
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

    if (!file_exists($mainScript)) {
        return;
    }

    $pythonExe = findCompatiblePython();
    if (!$pythonExe) {
        return;
    }

    // ⚡ OPTIMIZACIÓN: Iniciar en background SIN BLOQUEAR
    // Usar WScript.Shell para iniciar proceso verdaderamente asincrónico
    $command = 'cd /d ' . escapeshellarg($pythonApiDir) . ' && start "" /min ' . escapeshellarg($pythonExe) . ' -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload 2>nul';

    try {
        if (class_exists('COM')) {
            $shell = new COM('WScript.Shell');
            // Ejecutar en background sin esperar (Async = true, WindowStyle = 0 = hidden)
            $shell->Run($command, 0, false);
        } else {
            // Fallback si COM no disponible
            throw new Exception('COM class not available');
        }
    } catch (Exception $e) {
        // Fallback a popen o exec si COM no está disponible
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
