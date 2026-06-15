<?php
// Importa un volcado SQL a la base de datos Railway usando las variables de entorno activas.
$file = __DIR__ . '/../pecosol_db.sql';
if (!file_exists($file)) {
    fwrite(STDERR, "SQL dump no encontrado: $file\n");
    exit(1);
}
$dbHost = getenv('DB_HOST') ?: getenv('MYSQL_HOST');
$dbPort = getenv('DB_PORT') ?: getenv('MYSQL_PORT');
$dbName = getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE');
$dbUser = getenv('DB_USERNAME') ?: getenv('MYSQLUSER');
$dbPass = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD');
if (!$dbHost && ($publicUrl = getenv('MYSQL_PUBLIC_URL'))) {
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

// Drop all existing tables to make the Railway database match el volcado local.
$tables = [];
$result = $mysqli->query('SHOW TABLES');
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    $result->free();
}
if (!empty($tables)) {
    $dropList = implode(', ', array_map(function ($t) use ($mysqli) { return '`' . $mysqli->real_escape_string($t) . '`'; }, $tables));
    if (!$mysqli->query('SET FOREIGN_KEY_CHECKS=0')) {
        fwrite(STDERR, "Error deshabilitando FK checks: {$mysqli->error}\n");
        exit(1);
    }
    if (!$mysqli->query('DROP TABLE IF EXISTS ' . $dropList)) {
        fwrite(STDERR, "Error al eliminar tablas existentes: {$mysqli->error}\n");
        exit(1);
    }
    if (!$mysqli->query('SET FOREIGN_KEY_CHECKS=1')) {
        fwrite(STDERR, "Error reactivando FK checks: {$mysqli->error}\n");
        exit(1);
    }
    fwrite(STDOUT, "Tablas existentes eliminadas: " . implode(', ', $tables) . "\n");
}

$sql = file_get_contents($file);
if ($sql === false) {
    fwrite(STDERR, "No se pudo leer el archivo SQL.\n");
    exit(1);
}
if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, "Importación falló: {$mysqli->error}\n");
    exit(1);
}
do {
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
    if ($mysqli->errno) {
        fwrite(STDERR, "Error durante importación: {$mysqli->error}\n");
        exit(1);
    }
} while ($mysqli->more_results() && $mysqli->next_result());

fwrite(STDOUT, "Importación de base de datos completada.\n");
$mysqli->close();
