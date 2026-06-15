<?php
// scripts/compare_rows.php
// Compara filas entre la BD local y Railway por tabla y muestra diferencias

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

$tables = ['products','sales','stock_movements'];

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

function get_table_columns($pdo, $table) {
    $cols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

function get_primary_key_columns($pdo, $table) {
    $cols = [];
    $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cols[$row['Seq_in_index'] - 1] = $row['Column_name'];
    }
    ksort($cols);
    return array_values($cols);
}

function fetch_hashes($pdo, $table, $cols, $pkCols) {
    if (empty($cols)) return [];
    $colList = implode(', ', array_map(function($c){ return "COALESCE(CAST(`$c` AS CHAR), '')"; }, $cols));
    $concatCols = "CONCAT_WS('||', $colList)";

    if (!empty($pkCols)) {
        $pkExpr = implode(",'|',", array_map(function($c){ return "COALESCE(CAST(`$c` AS CHAR),'')"; }, $pkCols));
        $pkConcat = "CONCAT($pkExpr)";
        $sql = "SELECT $pkConcat AS pk, MD5($concatCols) AS h FROM `$table`";
        $stmt = $pdo->query($sql);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $out[$r['pk']] = $r['h'];
        }
        return $out;
    } else {
        // No primary key: return multiset of hashes with counts
        $sql = "SELECT MD5($concatCols) AS h, COUNT(*) AS c FROM `$table` GROUP BY h";
        $stmt = $pdo->query($sql);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $out[$r['h']] = intval($r['c']);
        }
        return $out;
    }
}

$localPdo = connect_db($local);
$railPdo  = connect_db($railway);

if (!$localPdo || !$railPdo) {
    echo "No se pudo conectar a ambas bases de datos. Abortando.\n";
    exit(1);
}

foreach ($tables as $t) {
    echo "\n== Comparando tabla: $t ==\n";
    $cols = get_table_columns($localPdo, $t);
    if (empty($cols)) {
        echo " Tabla no existe o sin columnas en local: $t\n";
        continue;
    }
    $pk = get_primary_key_columns($localPdo, $t);

    $localHashes = fetch_hashes($localPdo, $t, $cols, $pk);
    $railHashes  = fetch_hashes($railPdo,  $t, $cols, $pk);

    if (!empty($pk)) {
        $localKeys = array_keys($localHashes);
        $railKeys  = array_keys($railHashes);

        $missingInRail = array_diff($localKeys, $railKeys);
        $missingInLocal = array_diff($railKeys, $localKeys);
        $diffCount = 0;
        $diffRows = [];
        foreach ($localHashes as $k => $h) {
            if (isset($railHashes[$k]) && $railHashes[$k] !== $h) {
                $diffCount++;
                if (count($diffRows) < 10) $diffRows[] = $k;
            }
        }

        echo " PK columns: " . (!empty($pk) ? implode(',', $pk) : 'none') . "\n";
        echo " Local rows: " . count($localKeys) . " | Railway rows: " . count($railKeys) . "\n";
        echo " Faltantes en Railway: " . count($missingInRail) . "\n";
        echo " Faltantes en Local  : " . count($missingInLocal) . "\n";
        echo " Filas con cambios   : " . $diffCount . "\n";
        if ($diffCount > 0) {
            echo " Ejemplos PKs con diferencias (hasta 10): " . implode(', ', $diffRows) . "\n";
        }
    } else {
        // multisets
        $onlyLocal = [];
        $onlyRail  = [];
        foreach ($localHashes as $h => $c) {
            $r = $railHashes[$h] ?? 0;
            if ($c > $r) $onlyLocal[$h] = $c - $r;
            elseif ($r > $c) $onlyRail[$h] = $r - $c;
        }
        foreach ($railHashes as $h => $c) {
            if (!isset($localHashes[$h])) $onlyRail[$h] = ($onlyRail[$h] ?? 0) + $c;
        }
        echo " Sin PK — hashes distintos en local: " . count($onlyLocal) . " | en Railway: " . count($onlyRail) . "\n";
    }
}

echo "\nComparación completa.\n";

?>
