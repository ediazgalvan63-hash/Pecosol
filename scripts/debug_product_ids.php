<?php
// scripts/debug_product_ids.php
$local = new PDO("mysql:host=127.0.0.1;port=3306;dbname=pecosol_db;charset=utf8mb4", "root", "");
$local->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$railway = new PDO(
    "mysql:host=switchback.proxy.rlwy.net;port=10989;dbname=railway;charset=utf8mb4",
    "root",
    "LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya"
);
$railway->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "DEBUG: PRODUCT IDs\n";
echo "================================================================================\n\n";

$localIds = $local->query("SELECT id, name, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$railwayIds = $railway->query("SELECT id, name, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

echo "LOCAL PRODUCTS:\n";
echo sprintf("%-5s | %-35s | %-8s | %-8s\n", "ID", "Name", "Stock", "Min");
echo "------|--------------------------------------|----------|----------\n";
foreach ($localIds as $p) {
    echo sprintf("%-5d | %-35s | %-8d | %-8d\n", $p['id'], substr($p['name'], 0, 33), $p['stock'], $p['stock_minimum']);
}

echo "\nRAILWAY PRODUCTS:\n";
echo sprintf("%-5s | %-35s | %-8s | %-8s\n", "ID", "Name", "Stock", "Min");
echo "------|--------------------------------------|----------|----------\n";
foreach ($railwayIds as $p) {
    echo sprintf("%-5d | %-35s | %-8d | %-8d\n", $p['id'], substr($p['name'], 0, 33), $p['stock'], $p['stock_minimum']);
}
?>
