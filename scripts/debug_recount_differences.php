<?php
// scripts/debug_recount_differences.php
$local = new PDO("mysql:host=127.0.0.1;port=3306;dbname=pecosol_db;charset=utf8mb4", "root", "");
$local->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$railway = new PDO(
    "mysql:host=switchback.proxy.rlwy.net;port=10989;dbname=railway;charset=utf8mb4",
    "root",
    "LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya"
);
$railway->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "DEBUG: DIFERENCIAS EN PRODUCTOS\n";
echo "================================================================================\n\n";

$localProducts = $local->query("SELECT id, name, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$railwayProducts = $railway->query("SELECT id, name, stock, stock_minimum FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

foreach ($localProducts as $prod) {
    $id = $prod['id'];
    $railProd = array_filter($railwayProducts, fn($p) => $p['id'] == $id)[0] ?? null;
    
    if (!$railProd) {
        echo "ID $id: FALTANTE EN RAILWAY\n";
        continue;
    }
    
    if ($prod['stock'] != $railProd['stock'] || $prod['stock_minimum'] != $railProd['stock_minimum']) {
        echo "ID $id - {$prod['name']}:\n";
        echo "  Local:   stock={$prod['stock']}, mín={$prod['stock_minimum']}\n";
        echo "  Railway: stock={$railProd['stock']}, mín={$railProd['stock_minimum']}\n\n";
    }
}

// Contar registros
echo "================================================================================\n";
echo "Total productos local: " . count($localProducts) . "\n";
echo "Total productos railway: " . count($railwayProducts) . "\n";
?>
