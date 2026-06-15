<?php
try {
    $opts = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT=>5];
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=pecosol_db;charset=utf8mb4","root","", $opts);
    echo "PDO OK\n";
    $stmt = $pdo->query('SELECT COUNT(*) as c FROM products');
    echo 'products=' . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
?>
