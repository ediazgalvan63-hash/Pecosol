<?php
/**
 * Idempotent schema fix for production (Railway).
 * Run:
 *   railway run php scripts/ensure_schema.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function out(string $msg): void {
    echo $msg . PHP_EOL;
}

function tableExists(PDO $db, string $table): bool {
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':t' => $table]);
    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $db, string $table, string $column): bool {
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':t' => $table, ':c' => $column]);
    return (bool) $stmt->fetchColumn();
}

try {
    $db = Database::connect();
    out("Connected. DB_TIMEZONE=" . (defined('DB_TIMEZONE') ? DB_TIMEZONE : 'n/a') . " APP_TIMEZONE=" . (defined('APP_TIMEZONE') ? APP_TIMEZONE : 'n/a'));

    // 1) sales.client_name
    if (tableExists($db, 'sales') && !columnExists($db, 'sales', 'client_name')) {
        out("Adding sales.client_name...");
        $db->exec("ALTER TABLE sales ADD COLUMN client_name VARCHAR(120) NOT NULL DEFAULT 'Cliente General' AFTER total_price");
        out("OK: sales.client_name added.");
    } else {
        out("OK: sales.client_name already exists (or sales table missing).");
    }

    // 2) purchases
    if (!tableExists($db, 'purchases')) {
        out("Creating purchases table...");
        $db->exec("
            CREATE TABLE purchases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                user_id INT NOT NULL,
                quantity INT NOT NULL,
                supplier VARCHAR(120) NOT NULL,
                notes VARCHAR(255) DEFAULT NULL,
                purchase_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        out("OK: purchases created.");
    } else {
        out("OK: purchases already exists.");
    }

    // 3) work_orders
    if (!tableExists($db, 'work_orders')) {
        out("Creating work_orders table...");
        $db->exec("
            CREATE TABLE work_orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_name VARCHAR(120) NOT NULL,
                service_type VARCHAR(120) NOT NULL,
                technician_name VARCHAR(120) NOT NULL,
                materials_used TEXT DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pendiente',
                sale_id INT NULL,
                notes VARCHAR(255) DEFAULT NULL,
                created_by INT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        out("OK: work_orders created.");
    } else {
        out("OK: work_orders already exists.");
    }

    // 4) audit_logs
    if (!tableExists($db, 'audit_logs')) {
        out("Creating audit_logs table...");
        $db->exec("
            CREATE TABLE audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(30) NOT NULL,
                entity VARCHAR(40) NOT NULL,
                entity_id INT NULL,
                details VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        out("OK: audit_logs created.");
    } else {
        out("OK: audit_logs already exists.");
    }

    out("Done.");
} catch (Throwable $e) {
    out("ERROR: " . $e->getMessage());
    exit(1);
}

