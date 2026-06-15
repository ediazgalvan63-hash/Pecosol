<?php
declare(strict_types=1);

$local = new PDO(
    'mysql:host=127.0.0.1;port=3306;dbname=pecosol_db;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$remote = new PDO(
    'mysql:host=switchback.proxy.rlwy.net;port=10989;dbname=railway;charset=utf8mb4',
    'root',
    getenv('RAILWAY_DB_PASSWORD') ?: 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 30,
    ]
);

$localTables = $local->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

echo "SYNC RÁPIDO LOCAL → RAILWAY\n";
echo str_repeat('=', 50) . "\n";

$remote->exec('SET FOREIGN_KEY_CHECKS=0');
$remote->exec('SET UNIQUE_CHECKS=0');

foreach ($localTables as $table) {
    $remoteTables = $remote->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($table, $remoteTables, true)) {
        $createRow = $local->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        $remote->exec($createRow[1]);
        echo "CREATE $table\n";
    }
    $remote->exec("TRUNCATE TABLE `$table`");
}

$order = ['users', 'employees', 'products', 'purchases', 'sales', 'work_orders', 'stock_movements', 'audit_logs'];
foreach ($order as $table) {
    if (!in_array($table, $localTables, true)) {
        continue;
    }
    $rows = $local->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo "$table: 0\n";
        continue;
    }
    $columns = array_keys($rows[0]);
    $colList = implode(', ', array_map(fn($c) => "`$c`", $columns));
    $chunkSize = 50;
    $count = 0;
    foreach (array_chunk($rows, $chunkSize) as $chunk) {
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
    echo "$table: $count\n";
}

$remote->exec('SET FOREIGN_KEY_CHECKS=1');
$remote->exec('SET UNIQUE_CHECKS=1');

$checks = [
    'productos' => 'SELECT COUNT(*) FROM products',
    'reconteos' => "SELECT COUNT(*) FROM stock_movements WHERE notes LIKE '%reconteo%'",
    'usuarios' => 'SELECT COUNT(*) FROM users',
];
echo str_repeat('-', 50) . "\n";
foreach ($checks as $label => $sql) {
    $lv = $local->query($sql)->fetchColumn();
    $rv = $remote->query($sql)->fetchColumn();
    echo sprintf("%s local=%s railway=%s %s\n", $label, $lv, $rv, ($lv == $rv ? 'OK' : 'DIFF'));
}
