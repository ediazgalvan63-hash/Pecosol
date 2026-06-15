<?php
// scripts/check_railway_variants.php
$host = 'switchback.proxy.rlwy.net';
$port = 10989;
$user = 'root';
$pass = 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya';

$names = ['pecosol_db','railway'];

echo "Conectando a $host:$port...\n";

foreach ($names as $db) {
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "\nBase: $db\n";
        $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$db'")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
            echo "  (sin tablas)\n";
            continue;
        }
        foreach ($tables as $t) {
            $c = $pdo->query("SELECT COUNT(*) as c FROM `$t`")->fetch(PDO::FETCH_ASSOC)['c'];
            printf("  %-30s %8d\n", $t, $c);
        }
    } catch (Exception $e) {
        echo " No se puede conectar o consultar $db: " . $e->getMessage() . "\n";
    }
}

echo "\nListado completado.\n";
?>
