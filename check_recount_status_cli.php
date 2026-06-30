<?php
/**
 * check_recount_status_cli.php
 * 
 * Versión CLI para verificar estado del reconteo localmente
 * Uso: php check_recount_status_cli.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║ VERIFICACIÓN DE RECONTEO - LOCAL                              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

try {
    $conn = Database::connect();
    echo "✓ Conexión a BD local exitosa\n\n";
    
    // 1. Productos
    echo "📦 PRODUCTOS:\n";
    echo str_repeat("-", 60) . "\n";
    $stmt = $conn->query("SELECT COUNT(*) AS total FROM products");
    $productCount = $stmt->fetch(PDO::FETCH_OBJ)->total;
    echo "  Total de productos: " . $productCount . "\n";
    
    // 2. Stock total
    $stmt = $conn->query("SELECT COALESCE(SUM(stock), 0) AS total_stock FROM products");
    $totalStock = $stmt->fetch(PDO::FETCH_OBJ)->total_stock;
    echo "  Stock total en BD:  " . $totalStock . "\n\n";
    
    // 3. Reconteos
    echo "📋 RECONTEOS REGISTRADOS:\n";
    echo str_repeat("-", 60) . "\n";
    $stmt = $conn->query("
        SELECT COUNT(*) AS total FROM stock_movements 
        WHERE notes LIKE '%reconteo%'
    ");
    $recountCount = $stmt->fetch(PDO::FETCH_OBJ)->total;
    echo "  Total de reconteos: " . $recountCount . "\n\n";
    
    // 4. Últimos reconteos
    if ($recountCount > 0) {
        echo "📝 ÚLTIMOS 5 RECONTEOS:\n";
        echo str_repeat("-", 60) . "\n";
        
        $stmt = $conn->query("
            SELECT sm.id, p.name, sm.quantity_change, sm.movement_date 
            FROM stock_movements sm
            JOIN products p ON sm.product_id = p.id
            WHERE sm.notes LIKE '%reconteo%'
            ORDER BY sm.movement_date DESC
            LIMIT 5
        ");
        $recounts = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($recounts as $r) {
            $change = $r->quantity_change > 0 ? '+' . $r->quantity_change : $r->quantity_change;
            $date = date('d-m-Y H:i', strtotime($r->movement_date));
            printf("  ID %d: %-30s %s (en %s)\n", 
                $r->id, 
                substr($r->name, 0, 28), 
                str_pad($change, 6, " ", STR_PAD_LEFT),
                $date
            );
        }
    } else {
        echo "  ⚠️  No hay reconteos registrados aún\n";
    }
    echo "\n";
    
    // 5. Primeros 5 productos y su stock
    echo "📊 PRIMEROS 5 PRODUCTOS:\n";
    echo str_repeat("-", 60) . "\n";
    echo "  ID | Nombre                      | Stock\n";
    echo "  " . str_repeat("-", 57) . "\n";
    
    $stmt = $conn->query("SELECT id, name, stock FROM products ORDER BY id ASC LIMIT 5");
    $products = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    foreach ($products as $p) {
        printf("  %-2d | %-27s | %5d\n", $p->id, substr($p->name, 0, 25), $p->stock);
    }
    echo "\n";
    
    // 6. Configuración de entorno
    echo "🔧 CONFIGURACIÓN DE ENTORNO:\n";
    echo str_repeat("-", 60) . "\n";
    printf("  Base de datos: %s\n", getenv('DB_DATABASE') ?: 'pecosol_db (default)');
    printf("  Host DB:       %s\n", getenv('DB_HOST') ?: 'localhost (default)');
    printf("  APP_BASE_URL:  %s\n", getenv('APP_BASE_URL') ?: 'http://localhost/pecosol/ (default)');
    printf("  Timezone BD:   %s\n", getenv('DB_TIMEZONE') ?: 'America/Lima (local)');
    printf("  Railway:       %s\n", (getenv('RAILWAY_ENVIRONMENT') ?: getenv('RAILWAY_PROJECT_ID')) ? 'SI DETECTADO' : 'No detectado (local)');
    echo "\n";
    
    // 7. Resumen
    echo "✅ RESUMEN:\n";
    echo str_repeat("-", 60) . "\n";
    echo "  ✓ Conexión a BD funcionando\n";
    echo "  ✓ Productos cargados: " . $productCount . "\n";
    echo "  ✓ Reconteos registrados: " . $recountCount . "\n";
    echo "  ✓ Módulo de reconteo disponible en:\n";
    echo "    → http://localhost/pecosol/index.php?controller=admin&action=inventoryRecountForm\n";
    echo "\n";
    echo "📌 PRÓXIMO PASO:\n";
    echo "   Accede a Railway y verifica que estos datos coincidan allá.\n";
    echo "   Si son diferentes, necesitas sincronizar la BD.\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Verifica que:\n";
    echo "  - MySQL esté corriendo en XAMPP\n";
    echo "  - La BD 'pecosol_db' exista\n";
    echo "  - Las tablas 'products' y 'stock_movements' existan\n";
    echo "\n";
}
?>
