<?php
// scripts/check_sync.php
// Script no destructivo: compara conteos entre la BD local (XAMPP) y Railway

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
    'db'   => 'pecosol_db'
];

$tables = ['products','sales','purchases','stock_movements','employees','users','work_orders','inventory_movements','audit_logs'];

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
        fwrite(STDERR, "Error connecting to {$cfg['host']}: " . $e->getMessage() . "\n");
        return null;
    }
}

function get_counts($pdo, $tables) {
    $out = [];
    foreach ($tables as $t) {
        try {
            $row = $pdo->query("SELECT COUNT(*) AS c FROM `$t`")->fetch(PDO::FETCH_ASSOC);
            $out[$t] = intval($row['c']);
        } catch (Exception $e) {
            $out[$t] = null;
        }
    }
    return $out;
}

echo "Conectando a bases de datos...\n";
$localPdo = connect_db($local);
$railPdo  = connect_db($railway);

if (!$localPdo && !$railPdo) {
    echo "No hay conexiones válidas. Abortando.\n";
    exit(1);
}

echo "Obteniendo conteos...\n";
$localCounts = $localPdo ? get_counts($localPdo, $tables) : [];
$railCounts  = $railPdo  ? get_counts($railPdo, $tables) : [];

echo str_repeat('=',70) . "\n";
echo str_pad('Tabla',25) . str_pad('Local',10) . str_pad('Railway',12) . "Diff\n";
echo str_repeat('-',70) . "\n";
foreach ($tables as $t) {
    $l = array_key_exists($t, $localCounts) && $localCounts[$t] !== null ? $localCounts[$t] : 'N/A';
    $r = array_key_exists($t, $railCounts)  && $railCounts[$t] !== null ? $railCounts[$t]  : 'N/A';
    $diff = (is_numeric($l) && is_numeric($r)) ? $l - $r : 'N/A';
    printf("%-25s %8s %11s %8s\n", $t, $l, $r, $diff);
}
echo str_repeat('=',70) . "\n";

echo "Hecho. Si quieres, puedo sincronizar los cambios (operación destructiva).";

?>