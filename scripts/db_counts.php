<?php
require_once __DIR__ . '/../config/database.php';

$tables = [
    'users',
    'products',
    'sales',
    'purchases',
    'work_orders',
    'stock_movements',
    'audit_logs'
];

try {
    $pdo = Database::connect();
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            echo "{$table}: {$count}\n";
        } catch (Exception $e) {
            echo "{$table}: ERROR {$e->getMessage()}\n";
        }
    }
} catch (Exception $e) {
    echo "DATABASE CONNECTION ERROR: {$e->getMessage()}\n";
    exit(1);
}
