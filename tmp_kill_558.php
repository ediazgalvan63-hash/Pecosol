<?php
$dsn = 'mysql:host=switchback.proxy.rlwy.net;port=10989';
$user = 'root';
$pass = 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya';
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('KILL 558');
    echo "KILLED 558\n";
    $stmt = $pdo->query('SHOW PROCESSLIST');
    foreach ($stmt as $row) {
        echo implode(' | ', [$row['Id'], $row['User'], $row['Host'], $row['db'] ?? 'NULL', $row['Command'], $row['Time'], $row['State'] ?? 'NULL', $row['Info'] ?? 'NULL']) . "\n";
    }
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
?>
