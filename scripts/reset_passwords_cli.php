<?php
// scripts/reset_passwords_cli.php
// Resetea las contraseñas a '123456' usando variables de entorno de Railway (MYSQL_* o MYSQL_PUBLIC_URL)

$file = __FILE__;
$publicUrl = getenv('MYSQL_PUBLIC_URL') ?: getenv('MYSQL_PUBLIC_URL');
$dbHost = getenv('MYSQL_HOST') ?: '';
$dbPort = getenv('MYSQL_PORT') ?: '';
$dbName = getenv('MYSQLDATABASE') ?: '';
$dbUser = getenv('MYSQLUSER') ?: '';
$dbPass = getenv('MYSQLPASSWORD') ?: '';

if (empty($dbHost) && $publicUrl) {
    $parts = parse_url($publicUrl);
    if ($parts !== false) {
        $dbHost = $parts['host'] ?? $dbHost;
        $dbPort = $parts['port'] ?? $dbPort;
        $dbUser = $parts['user'] ?? $dbUser;
        $dbPass = $parts['pass'] ?? $dbPass;
        $dbName = ltrim($parts['path'] ?? '', '/') ?: $dbName;
    }
}

if (!$dbHost || !$dbPort || !$dbName || !$dbUser) {
    fwrite(STDERR, "Faltan variables de conexión MySQL en el entorno.\n");
    exit(1);
}

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Error de conexión: {$mysqli->connect_error}\n");
    exit(1);
}

$newHash = password_hash('123456', PASSWORD_BCRYPT);
if (!$stmt = $mysqli->prepare('UPDATE users SET password = ?')) {
    fwrite(STDERR, "Preparación falló: {$mysqli->error}\n");
    exit(1);
}
$stmt->bind_param('s', $newHash);
if (!$stmt->execute()) {
    fwrite(STDERR, "Ejecución falló: {$stmt->error}\n");
    exit(1);
}

fwrite(STDOUT, "Contraseñas reseteadas a '123456' para todos los usuarios.\n");
$mysqli->close();
