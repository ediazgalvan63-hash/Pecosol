<?php
$dsn = 'mysql:host=switchback.proxy.rlwy.net;port=10989';
$user = 'root';
$pass = 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya';
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query('SHOW DATABASES');
    foreach ($stmt as $row) {
        echo $row[0] . "\n";
    }
    echo "CONNECT_OK\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
