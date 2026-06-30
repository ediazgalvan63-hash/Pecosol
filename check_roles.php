<?php
require 'config/database.php';

$conn = Database::connect();

echo "=== ESTRUCTURA DE TABLA 'users' ===\n\n";
$result = $conn->query('DESCRIBE users');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") - " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}

echo "\n=== USUARIOS EN LA BD ===\n\n";
$result = $conn->query('SELECT id, username, email, role FROM users');
$users = $result->fetchAll(PDO::FETCH_ASSOC);
foreach($users as $user) {
    echo "  ID:" . $user['id'] . " | " . $user['username'] . " (" . $user['role'] . ") | " . $user['email'] . "\n";
}

echo "\n=== INFORMACIÓN GENERAL DE LA BD ===\n\n";
$tables = ['users', 'products', 'sales', 'purchases', 'employees', 'stock_movements', 'work_orders', 'audit_logs'];
foreach($tables as $table) {
    $result = $conn->query('SELECT COUNT(*) as cnt FROM ' . $table);
    $count = $result->fetch(PDO::FETCH_ASSOC);
    echo $table . ": " . $count['cnt'] . " registros\n";
}
?>
