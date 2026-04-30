<?php
// config/config.php

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
 */
define('CHATBOT_API_URL', getenv('CHATBOT_API_URL') ?: 'http://127.0.0.1:8000/api/chat');

// Nombre del proyecto
define('PROJECT_NAME', 'Pecosol');
