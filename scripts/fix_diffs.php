<?php
// scripts/fix_diffs.php
// Copia los valores de las filas locales a Railway para los PKs detectados

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

function get_primary_key_columns($pdo, $table) {
    $cols = [];
    $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cols[$row['Seq_in_index'] - 1] = $row['Column_name'];
    }
    ksort($cols);
    return array_values($cols);
}

function fetch_row($pdo, $table, $pkCols, $pkValue) {
    $pk = $pkCols[0];
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = :v LIMIT 1");
    $stmt->execute([':v' => $pkValue]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$localPdo = connect_db($local);
$railPdo  = connect_db($railway);

if (!$localPdo || !$railPdo) {
    echo "No se pudo conectar a ambas bases de datos. Abortando.\n";
    exit(1);
}

$fixed = 0;
foreach ($targets as $table => $pks) {
    echo "\n== Arreglando tabla: $table ==\n";
    $pkCols = get_primary_key_columns($localPdo, $table);
    if (empty($pkCols)) {
        echo " Tabla sin PK detectable: $table. Saltando.\n";
        continue;
    }
    $pk = $pkCols[0];
    foreach ($pks as $id) {
        $lrow = fetch_row($localPdo, $table, $pkCols, $id);
        if (!$lrow) {
            echo "  Fila $id ausente en local. Saltando.\n";
            continue;
        }
        $cols = array_keys($lrow);
        $set = [];
        $params = [];
        foreach ($cols as $c) {
            if ($c === $pk) continue;
            $set[] = "`$c` = :$c";
            $params[":$c"] = $lrow[$c];
        }
        $params[':pkval'] = $id;
        $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE `$pk` = :pkval";
        $stmt = $railPdo->prepare($sql);
        try {
            $stmt->execute($params);
            echo "  Actualizada fila $id en Railway.\n";
            $fixed++;
        } catch (Exception $e) {
            echo "  Error actualizando $table/$id: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nTotal filas actualizadas: $fixed\n";

?>
