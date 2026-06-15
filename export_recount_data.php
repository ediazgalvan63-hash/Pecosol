<?php
/**
 * export_recount_data.php
 * Exporta productos y movimientos de reconteo a SQL
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $conn = Database::connect();
    
    // Iniciar SQL
    $sql = "-- ============================================================\n";
    $sql .= "-- EXPORTACIÓN DE RECONTEO: Productos + Stock Movements\n";
    $sql .= "-- Generado: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- ============================================================\n\n";
    
    // Truncate tablas (opcional, comentado)
    $sql .= "-- TRUNCATE TABLE products;\n";
    $sql .= "-- TRUNCATE TABLE stock_movements;\n\n";
    
    // ============================================================
    // PRODUCTS
    // ============================================================
    $sql .= "-- ============================================================\n";
    $sql .= "-- TABLA: products\n";
    $sql .= "-- ============================================================\n";
    
    $stmt = $conn->query("SHOW CREATE TABLE products");
    $table = $stmt->fetch(PDO::FETCH_ASSOC);
    $sql .= $table['Create Table'] . ";\n\n";
    
    // Datos
    $stmt = $conn->query("SELECT * FROM products ORDER BY id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($products) > 0) {
        $sql .= "INSERT INTO products (id, name, description, price, stock, stock_minimum) VALUES\n";
        
        foreach ($products as $idx => $row) {
            $name = addslashes($row['name']);
            $desc = addslashes($row['description'] ?? '');
            $price = (float)$row['price'];
            $stock = (int)$row['stock'];
            $min = (int)($row['stock_minimum'] ?? 0);
            
            $sql .= "({$row['id']}, '{$name}', '{$desc}', {$price}, {$stock}, {$min})";
            
            if ($idx < count($products) - 1) {
                $sql .= ",\n";
            } else {
                $sql .= ";\n";
            }
        }
    }
    
    $sql .= "\n";
    
    // ============================================================
    // STOCK_MOVEMENTS
    // ============================================================
    $sql .= "-- ============================================================\n";
    $sql .= "-- TABLA: stock_movements (solo reconteos)\n";
    $sql .= "-- ============================================================\n";
    
    $stmt = $conn->query("SHOW CREATE TABLE stock_movements");
    $table = $stmt->fetch(PDO::FETCH_ASSOC);
    $sql .= $table['Create Table'] . ";\n\n";
    
    // Datos - solo reconteos
    $stmt = $conn->query("SELECT * FROM stock_movements WHERE notes LIKE '%reconteo%' ORDER BY id");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($movements) > 0) {
        $sql .= "INSERT INTO stock_movements (id, product_id, user_id, quantity_change, movement_type, notes, movement_date) VALUES\n";
        
        foreach ($movements as $idx => $row) {
            $notes = addslashes($row['notes']);
            $date = $row['movement_date'];
            
            $sql .= "(";
            $sql .= "{$row['id']}, ";
            $sql .= "{$row['product_id']}, ";
            $sql .= "{$row['user_id']}, ";
            $sql .= "{$row['quantity_change']}, ";
            $sql .= "'{$row['movement_type']}', ";
            $sql .= "'{$notes}', ";
            $sql .= "'{$date}'";
            $sql .= ")";
            
            if ($idx < count($movements) - 1) {
                $sql .= ",\n";
            } else {
                $sql .= ";\n";
            }
        }
    }
    
    $sql .= "\n-- Fin de exportación\n";
    
    // Guardar archivo
    file_put_contents(__DIR__ . '/recount_sync.sql', $sql);
    
    echo "✓ Exportación completada\n";
    echo "  Archivo: recount_sync.sql\n";
    echo "  Tamaño: " . filesize(__DIR__ . '/recount_sync.sql') . " bytes\n";
    echo "  Productos: " . count($products) . "\n";
    echo "  Reconteos: " . count($movements) . "\n\n";
    
    echo "📋 PRIMERAS LÍNEAS DEL SQL:\n";
    echo str_repeat("─", 60) . "\n";
    echo implode("\n", array_slice(explode("\n", $sql), 0, 30));
    echo "\n" . str_repeat("─", 60) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
