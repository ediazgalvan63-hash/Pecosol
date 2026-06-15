<?php
/**
 * apply_roles_change.php
 *
 * Utilidad CLI para respaldar la tabla `users` y aplicar:
 *   ALTER TABLE users MODIFY COLUMN role ENUM('admin','comercial','supervisor') NOT NULL;
 *
 * Uso (recomendado, sin exponer password en el comando):
 *   export DB_HOST=... DB_PORT=... DB_NAME=... DB_USER=... DB_PASS=...
 *   php scripts/apply_roles_change.php --yes
 *
 * También se aceptan argumentos posicionales:
 *   php scripts/apply_roles_change.php host port db user password --yes
 */

function usage()
{
    echo "Usage:\n";
    echo "  Provide DB creds via env vars DB_HOST DB_PORT DB_NAME DB_USER DB_PASS and run:\n";
    echo "    php scripts/apply_roles_change.php --yes\n";
    echo "  Or pass as args:\n";
    echo "    php scripts/apply_roles_change.php host port db user pass --yes\n";
    exit(1);
}

$argv_copy = $argv;
array_shift($argv_copy);

$force = in_array('--yes', $argv_copy, true) || in_array('-y', $argv_copy, true);

// Remove force flags from argv_copy for counting
$argv_args = array_values(array_filter($argv_copy, fn($a) => $a !== '--yes' && $a !== '-y'));

if (count($argv_args) === 5) {
    [$host, $port, $db, $user, $pass] = $argv_args;
} else {
    $host = getenv('DB_HOST') ?: getenv('RAILWAY_DB_HOST') ?: '';
    $port = getenv('DB_PORT') ?: getenv('RAILWAY_DB_PORT') ?: '3306';
    $db   = getenv('DB_NAME') ?: getenv('RAILWAY_DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: getenv('RAILWAY_DB_USER') ?: '';
    $pass = getenv('DB_PASS') ?: getenv('RAILWAY_DB_PASSWORD') ?: '';
}

if (empty($host) || empty($db) || empty($user)) {
    usage();
}

echo "Connecting to DB {$host}:{$port} / {$db} as {$user}\n";

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(2);
}

// 1) Read distinct role values in DB
$stmt = $pdo->query("SELECT DISTINCT role FROM users");
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Existing roles in DB: " . implode(', ', $roles) . "\n";

$allowed = ['admin', 'comercial', 'supervisor'];
$bad = array_values(array_filter($roles, fn($r) => !in_array($r, $allowed, true)));
if ($bad) {
    echo "WARNING: Found role values not in target ENUM: " . implode(', ', $bad) . "\n";
    echo "You must decide how to handle them (update or remove) before altering the enum.\n";
    echo "Options:\n  - Update offending rows to one of the allowed roles, then re-run this script.\n  - Run with --force to attempt ALTER anyway (may fail).\n";
    if (!$force) {
        echo "Aborting due to unexpected role values. Rerun with --yes to force.\n";
        exit(3);
    }
}

// 2) Backup users table to JSON file
$backupFile = __DIR__ . '/users_backup_' . date('Ymd_His') . '.json';
echo "Creating backup file: {$backupFile}\n";
$data = $pdo->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
file_put_contents($backupFile, json_encode($data, JSON_PRETTY_PRINT));

// 3) Apply ALTER TABLE
echo "Applying ALTER TABLE to set role ENUM to (admin, comercial, supervisor)...\n";
try {
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','comercial','supervisor') NOT NULL");
    echo "ALTER TABLE executed successfully.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "ALTER TABLE failed: " . $e->getMessage() . "\n");
    echo "Restoring backup not automatic; review {$backupFile} to recover.\n";
    exit(4);
}

echo "Done. Verified enum; you can now create multiple users with the same role.\n";
exit(0);
