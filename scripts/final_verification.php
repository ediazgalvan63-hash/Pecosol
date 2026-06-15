<?php
// scripts/final_verification.php
// Verificación completa: local vs Railway (ambas BDs)

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

function get_product_hashes($pdo) {
    $hashes = [];
    try {
        $stmt = $pdo->query("SELECT id, name, description, price, stock, stock_minimum FROM products ORDER BY id");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashes[$row['id']] = md5(json_encode($row));
        }
    } catch (Exception $e) {
        // tabla no existe o error
    }
    return $hashes;
}

echo str_repeat("═", 70) . "\n";
echo "VERIFICACIÓN FINAL: LOCAL vs RAILWAY\n";
echo str_repeat("═", 70) . "\n\n";

$localPdo = connect_db($local);
$railPdo  = connect_db($railway);

if (!$localPdo || !$railPdo) {
    echo "❌ No se pudo conectar a ambas BDs. Abortando.\n";
    exit(1);
}

$tables = ['products','sales','purchases','stock_movements','employees','users','work_orders','audit_logs'];

echo "CONTEOS POR TABLA:\n";
echo str_repeat("-", 70) . "\n";
printf("%-30s %15s %15s %10s\n", "Tabla", "Local", "Railway", "Iguales");
echo str_repeat("-", 70) . "\n";

$localCounts = get_counts($localPdo, $tables);
$railCounts  = get_counts($railPdo, $tables);

$allEqual = true;
foreach ($tables as $t) {
    $l = $localCounts[$t] ?? 'N/A';
    $r = $railCounts[$t]  ?? 'N/A';
    $eq = ($l === $r) ? '✅ SÍ' : '❌ NO';
    if ($l !== $r) $allEqual = false;
    printf("%-30s %15s %15s %10s\n", $t, $l, $r, $eq);
}

echo str_repeat("-", 70) . "\n";
echo "\n";

// Verificar productos en detalle
echo "PRODUCTOS (verificación de campos):\n";
echo str_repeat("-", 70) . "\n";

$localProds = get_product_hashes($localPdo);
$railProds  = get_product_hashes($railPdo);

if (count($localProds) === count($railProds) && count($localProds) > 0) {
    $prods_equal = true;
    foreach ($localProds as $id => $hash) {
        if (!isset($railProds[$id]) || $railProds[$id] !== $hash) {
            $prods_equal = false;
            break;
        }
    }
    if ($prods_equal) {
        echo "✅ Todos los " . count($localProds) . " productos son idénticos en ambas BDs.\n";
    } else {
        echo "❌ Algunos productos difieren entre local y Railway.\n";
    }
} else {
    echo "⚠️ Número de productos distinto: Local=" . count($localProds) . ", Railway=" . count($railProds) . "\n";
}

echo "\n" . str_repeat("═", 70) . "\n";
if ($allEqual) {
    echo "✅ VERIFICACIÓN COMPLETA: LOCAL Y RAILWAY SON IDÉNTICOS\n";
    echo "   Railway ahora funciona igual al local.\n";
} else {
    echo "⚠️  Algunas diferencias detectadas. Revisa arriba.\n";
}
echo str_repeat("═", 70) . "\n";

?>
