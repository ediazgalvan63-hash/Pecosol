<?php
// DEBUG: Mostrar qué REQUEST_URI ve el servidor
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'NOT SET',
    'REDIRECT_URL' => $_SERVER['REDIRECT_URL'] ?? 'NOT SET',
    'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'NOT SET',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'NOT SET',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'NOT SET',
    'GET_url' => $_GET['url'] ?? 'NOT SET',
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'NOT SET',
    'all_get' => $_GET,
    'parsed_uri' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
