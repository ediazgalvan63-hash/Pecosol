<?php
declare(strict_types=1);

/**
 * Script legado para mantener un usuario por rol principal.
 * Reasigna registros huérfanos al admin y sincroniza a Railway si se pasa --sync.
 */

// Script deprecated: previously enforced keeping only three users and
// altering the `users.role` enum. To avoid accidental deletions or
// schema changes on Railway, this script is now a no-op.

echo "keep_three_users.php is deprecated and will not modify the database.\n";
exit(0);

function keepUsers(PDO $db, array $allowedRoles): array
{
    $users = $db->query('SELECT id, username, role, email FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    echo "Usuarios antes: " . count($users) . "\n";
    foreach ($users as $user) {
        echo "  - ID {$user['id']}: {$user['username']} ({$user['role']})\n";
    }

    $keepByRole = [];
    foreach ($users as $user) {
        if (!in_array($user['role'], $allowedRoles, true)) {
            continue;
        }
        if (!isset($keepByRole[$user['role']])) {
            $keepByRole[$user['role']] = (int) $user['id'];
        }
    }

    foreach ($allowedRoles as $role) {
        if (!isset($keepByRole[$role])) {
            throw new RuntimeException("Falta un usuario con rol '$role'.");
        }
    }

    $keepIds = array_values($keepByRole);
    $adminId = $keepByRole['admin'];
    $deleteIds = [];

    foreach ($users as $user) {
        $id = (int) $user['id'];
        if (!in_array($id, $keepIds, true)) {
            $deleteIds[] = $id;
        }
    }

    if (!$deleteIds) {
        echo "\nYa solo existen los 3 usuarios requeridos.\n";
        return $keepByRole;
    }

    echo "\nConservar:\n";
    foreach ($keepByRole as $role => $id) {
        echo "  - $role => ID $id\n";
    }
    echo "Eliminar IDs: " . implode(', ', $deleteIds) . "\n";

    $tablesWithUserId = [
        'audit_logs',
        'purchases',
        'sales',
        'stock_movements',
        'work_orders',
    ];

    $db->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($deleteIds as $deleteId) {
        foreach ($tablesWithUserId as $table) {
            $stmt = $db->prepare("UPDATE `$table` SET user_id = ? WHERE user_id = ?");
            $stmt->execute([$adminId, $deleteId]);
            $updated = $stmt->rowCount();
            if ($updated > 0) {
                echo "  $table: reasignados $updated registros de ID $deleteId a admin ($adminId)\n";
            }
        }
    }

    $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
    $db->prepare("DELETE FROM employees WHERE user_id IN ($placeholders)")->execute($deleteIds);
    $db->prepare("DELETE FROM users WHERE id IN ($placeholders)")->execute($deleteIds);
    $db->exec('SET FOREIGN_KEY_CHECKS=1');

    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','employee','gerencia','comercial','logistica','finanzas','estrategico','supervisor') NOT NULL");

    $remaining = $db->query('SELECT id, username, role, email FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    echo "\nUsuarios después: " . count($remaining) . "\n";
    foreach ($remaining as $user) {
        echo "  - ID {$user['id']}: {$user['username']} ({$user['role']})\n";
    }

    return $keepByRole;
}

function syncLocalToRailway(PDO $local): void
{
    $remote = new PDO(
        'mysql:host=switchback.proxy.rlwy.net;port=10989;dbname=railway;charset=utf8mb4',
        'root',
        getenv('RAILWAY_DB_PASSWORD') ?: 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30,
        ]
    );

    echo "\nSincronizando LOCAL -> RAILWAY...\n";
    $remote->exec('SET FOREIGN_KEY_CHECKS=0');
    $remote->exec('SET UNIQUE_CHECKS=0');

    $tables = ['users', 'employees', 'products', 'purchases', 'sales', 'work_orders', 'stock_movements', 'audit_logs'];
    foreach ($tables as $table) {
        $remote->exec("TRUNCATE TABLE `$table`");
        $rows = $local->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            echo "  $table: 0\n";
            continue;
        }
        $columns = array_keys($rows[0]);
        $colList = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $count = 0;
        foreach (array_chunk($rows, 50) as $chunk) {
            $values = [];
            $params = [];
            foreach ($chunk as $row) {
                $values[] = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
                foreach ($columns as $col) {
                    $params[] = $row[$col];
                }
            }
            $sql = "INSERT INTO `$table` ($colList) VALUES " . implode(', ', $values);
            $stmt = $remote->prepare($sql);
            $stmt->execute($params);
            $count += count($chunk);
        }
        echo "  $table: $count\n";
    }

    $remote->exec('SET FOREIGN_KEY_CHECKS=1');
    $remote->exec('SET UNIQUE_CHECKS=1');

    $localUsers = $local->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $remoteUsers = $remote->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "Usuarios local=$localUsers railway=$remoteUsers " . ($localUsers == $remoteUsers ? 'OK' : 'DIFF') . "\n";
}

echo "=== MANTENER SOLO 3 USUARIOS ===\n\n";
keepUsers($local, $allowedRoles);

if ($syncToRailway) {
    syncLocalToRailway($local);
}

echo "\nListo.\n";

$log = ob_get_contents();
file_put_contents(__DIR__ . '/keep_three_users_out.txt', $log);
ob_end_flush();
