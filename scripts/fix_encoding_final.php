<?php
// scripts/fix_encoding_final.php
$railway = [
    'host' => 'switchback.proxy.rlwy.net',
    'port' => 10989,
    'user' => 'root',
    'pass' => 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
    'db'   => 'railway'
];

$pdo = new PDO(
    "mysql:host={$railway['host']};port={$railway['port']};dbname={$railway['db']};charset=utf8mb4",
    $railway['user'],
    $railway['pass']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obtener la descripción correcta desde local
$local = new PDO("mysql:host=127.0.0.1;port=3306;dbname=pecosol_db;charset=utf8mb4", "root", "");
$local->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$localDesc = $local->query("SELECT description FROM products WHERE id=16")->fetch(PDO::FETCH_ASSOC)['description'];

echo "Descripción local: $localDesc\n";

$stmt = $pdo->prepare("UPDATE products SET description = ? WHERE id = 16");
$stmt->execute([$localDesc]);

echo "✅ Producto 16 actualizado en Railway\n";

// Verificar
$railDesc = $pdo->query("SELECT description FROM products WHERE id=16")->fetch(PDO::FETCH_ASSOC)['description'];
echo "Descripción Railway: $railDesc\n";

if ($localDesc === $railDesc) {
    echo "✅ IDÉNTICAS\n";
} else {
    echo "❌ Aún diferentes\n";
    echo "  Local bytes:   " . bin2hex($localDesc) . "\n";
    echo "  Railway bytes: " . bin2hex($railDesc) . "\n";
}
?>
