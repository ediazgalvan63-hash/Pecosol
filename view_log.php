<?php
// view_log.php - Ver el log de requests
header('Content-Type: text/plain; charset=utf-8');

$logFile = __DIR__ . '/request_log.txt';
if (file_exists($logFile)) {
    echo "=== REQUEST LOG ===\n";
    echo file_get_contents($logFile);
} else {
    echo "Log file not found yet. Make a request to /health first.";
}
?>
