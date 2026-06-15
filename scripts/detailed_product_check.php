<?php
// scripts/detailed_product_check.php

$local = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'root',
    'pass' => '',
    'db'   => 'pecosol_db'
];

$railway = [
    'host' => 'switchback.proxy.rlwy.net',
    'port' => 10989,
    'user' => 'root',
    'pass' => 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
    'db'   => 'railway'
];

function connect_db($cfg) {
    try {
        $pdo = new PDO(
            "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['db']}",
            $cfg['user'],
            $cfg['pass'],
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4')
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
        return null;
    }
}

$localPdo = connect_db($local);
$railPdo  = connect_db($railway);

if (!$localPdo || !$railPdo) {
    echo "❌ No se pudo conectar.\n";
    exit(1);
}

echo "COMPARACIÓN DETALLADA DE PRODUCTOS:\n";
echo str_repeat("=", 80) . "\n\n";

$localProds = $localPdo->query("SELECT id, name, description, price, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$railProds  = $railPdo->query("SELECT id, name, description, price, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$diffs = 0;
foreach ($localProds as $idx => $lp) {
    $rp = $railProds[$idx] ?? null;
    if (!$rp) {
        echo "❌ Producto local ID {$lp['id']} no existe en Railway\n";
        $diffs++;
        continue;
    }
    
    $cols = ['name', 'description', 'price', 'stock', 'stock_minimum'];
    $has_diff = false;
    foreach ($cols as $col) {
        if ((string)$lp[$col] !== (string)$rp[$col]) {
            if (!$has_diff) {
                echo "Producto ID {$lp['id']} - {$lp['name']}:\n";
                $has_diff = true;
                $diffs++;
            }
            printf("  %-20s | Local: %-30s | Railway: %-30s\n", $col, $lp[$col], $rp[$col]);
        }
    }
}

echo str_repeat("=", 80) . "\n";
if ($diffs === 0) {
    echo "✅ TODOS LOS PRODUCTOS SON IDÉNTICOS\n";
} else {
    echo "⚠️  $diffs producto(s) con diferencias detectadas.\n";
}

?>
