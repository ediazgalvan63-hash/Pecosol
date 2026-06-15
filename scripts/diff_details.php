<?php
// scripts/diff_details.php
// Muestra diferencias por columna para filas específicas entre local y Railway

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

$targets = [
    'products' => ['16'],
    'stock_movements' => ['131','132','133','134','135','136'],
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

function fetch_row($pdo, $table, $pkCols, $pkValue) {
    // Build WHERE for composite PK or single
    if (is_array($pkCols) && count($pkCols) > 1) {
        // Not expected in our targets; skip
        return null;
    }
    $pk = $pkCols[0];
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = :v LIMIT 1");
    $stmt->execute([':v' => $pkValue]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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

$localPdo = connect_db($local);
$railPdo  = connect_db($railway);

if (!$localPdo || !$railPdo) {
    echo "No se pudo conectar a ambas bases de datos. Abortando.\n";
    exit(1);
}

foreach ($targets as $table => $pks) {
    echo "\n== Detalles para tabla: $table ==\n";
    $pkCols = get_primary_key_columns($localPdo, $table);
    if (empty($pkCols)) {
        echo " Tabla sin PK detectable: $table. No se puede comparar por fila.\n";
        continue;
    }
    foreach ($pks as $pk) {
        echo "\n-- PK: $pk --\n";
        $l = fetch_row($localPdo, $table, $pkCols, $pk);
        $r = fetch_row($railPdo,  $table, $pkCols, $pk);
        if (!$l) {
            echo "  Fila ausente en local (pk=$pk)\n";
            continue;
        }
        if (!$r) {
            echo "  Fila ausente en Railway (pk=$pk)\n";
            continue;
        }
        $cols = array_keys($l + $r);
        foreach ($cols as $c) {
            $lv = array_key_exists($c, $l) ? $l[$c] : 'NULL';
            $rv = array_key_exists($c, $r) ? $r[$c] : 'NULL';
            if ((string)$lv !== (string)$rv) {
                printf("  %-25s | %-30s | %-30s\n", $c, $lv, $rv);
            }
        }
    }
}

echo "\nInforme detallado completado.\n";

?>
