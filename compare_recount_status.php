<?php
/**
 * compare_recount_status.php
 * 
 * Compara el módulo de reconteo entre LOCAL y RAILWAY
 * Verifica:
 * 1. Si los productos son iguales
 * 2. Si los stock coinciden
 * 3. Si los movimientos de reconteo están sincronizados
 * 4. Errores de acceso/configuración
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparación de Reconteo: Local vs Railway</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e; 
            color: #eaeaea; 
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #00ffff; margin-bottom: 30px; }
        .card { 
            background: #16213e; 
            border: 1px solid rgba(0, 255, 240, 0.2); 
            border-radius: 8px; 
            padding: 20px; 
            margin-bottom: 20px;
        }
        .status-ok { color: #9af7c8; font-weight: bold; }
        .status-warning { color: #ffd93d; font-weight: bold; }
        .status-error { color: #ff9ea4; font-weight: bold; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0;
        }
        th, td { 
            text-align: left; 
            padding: 10px; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th { 
            background: #0f3460; 
            color: #a0fdfd; 
            font-weight: 600;
        }
        tr:nth-child(even) { background: rgba(255, 255, 255, 0.02); }
        .summary { 
            background: #173f2f; 
            border-left: 4px solid #00ff88; 
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-box { 
            background: #3a1010; 
            border-left: 4px solid #ff6b6b; 
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .code { 
            background: #0d1b2a; 
            padding: 10px; 
            border-radius: 4px; 
            font-family: 'Courier New', monospace;
            overflow: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📊 Comparación de Reconteo: Local vs Railway</h1>

    <?php
    // ============================================================
    // 1. Obtener datos de LOCAL
    // ============================================================
    echo '<div class="card">';
    echo '<h2>🔍 Estado Local</h2>';
    
    try {
        $conn = Database::connect();
        
        // Contar productos
        $stmt = $conn->query("SELECT COUNT(*) AS total FROM products");
        $productCount = $stmt->fetch(PDO::FETCH_OBJ)->total;
        
        // Contar movimientos de reconteo
        $stmt = $conn->query("SELECT COUNT(*) AS total FROM stock_movements WHERE notes LIKE '%reconteo%'");
        $recountCount = $stmt->fetch(PDO::FETCH_OBJ)->total;
        
        // Stock total
        $stmt = $conn->query("SELECT COALESCE(SUM(stock), 0) AS total_stock FROM products");
        $totalStock = $stmt->fetch(PDO::FETCH_OBJ)->total_stock;
        
        echo '<table>';
        echo '<tr><th>Métrica</th><th>Valor</th></tr>';
        echo '<tr><td>Productos totales</td><td><span class="status-ok">' . $productCount . '</span></td></tr>';
        echo '<tr><td>Movimientos de reconteo</td><td><span class="status-ok">' . $recountCount . '</span></td></tr>';
        echo '<tr><td>Stock total en BD</td><td><span class="status-ok">' . $totalStock . '</span></td></tr>';
        echo '</table>';
        
        // Últimos reconteos
        echo '<h3>Últimos reconteos aplicados:</h3>';
        $stmt = $conn->query("
            SELECT sm.id, p.name, sm.quantity_change, sm.movement_date 
            FROM stock_movements sm
            JOIN products p ON sm.product_id = p.id
            WHERE sm.notes LIKE '%reconteo%'
            ORDER BY sm.movement_date DESC
            LIMIT 5
        ");
        $recounts = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        if (count($recounts) > 0) {
            echo '<table>';
            echo '<tr><th>Producto</th><th>Cambio</th><th>Fecha</th></tr>';
            foreach ($recounts as $r) {
                $change = $r->quantity_change > 0 ? '+' . $r->quantity_change : $r->quantity_change;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($r->name) . '</td>';
                echo '<td>' . $change . '</td>';
                echo '<td>' . date('d-m-Y H:i', strtotime($r->movement_date)) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p><span class="status-warning">⚠️ No hay reconteos registrados aún</span></p>';
        }
        
        echo '</div>';
    } catch (Exception $e) {
        echo '<div class="error-box">';
        echo '<strong>❌ Error conectando a LOCAL:</strong><br>';
        echo htmlspecialchars($e->getMessage());
        echo '</div>';
    }
    
    // ============================================================
    // 2. Verificar configuración para Railway
    // ============================================================
    echo '<div class="card">';
    echo '<h2>🚂 Configuración Railway Detectada</h2>';
    
    $railwayVars = [
        'RAILWAY_ENVIRONMENT' => getenv('RAILWAY_ENVIRONMENT'),
        'RAILWAY_PROJECT_ID' => getenv('RAILWAY_PROJECT_ID'),
        'DB_HOST' => getenv('DB_HOST'),
        'DB_DATABASE' => getenv('DB_DATABASE'),
        'APP_BASE_URL' => getenv('APP_BASE_URL'),
        'DB_TIMEZONE' => getenv('DB_TIMEZONE'),
    ];
    
    $isRailway = (bool) (getenv('RAILWAY_ENVIRONMENT') ?: getenv('RAILWAY_PROJECT_ID'));
    
    echo '<p><strong>Ambiente detectado:</strong> ' . ($isRailway ? '<span class="status-ok">RAILWAY</span>' : '<span class="status-warning">LOCAL</span>') . '</p>';
    
    echo '<h3>Variables críticas:</h3>';
    echo '<table>';
    echo '<tr><th>Variable</th><th>Valor</th><th>Estado</th></tr>';
    
    foreach ($railwayVars as $key => $value) {
        $status = $value ? '<span class="status-ok">✓ Configurada</span>' : '<span class="status-warning">⚠️ No configurada</span>';
        echo '<tr>';
        echo '<td><code>' . htmlspecialchars($key) . '</code></td>';
        echo '<td><code>' . htmlspecialchars($value ?: '(vacío)') . '</code></td>';
        echo '<td>' . $status . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    
    // ============================================================
    // 3. Script de comparación a Railway
    // ============================================================
    echo '<div class="card">';
    echo '<h2>🔗 Para Comparar con Railway</h2>';
    echo '<p>Necesitas acceso a la BD de Railway. Si lo tienes, ejecuta este comando:</p>';
    
    $railwayExample = <<<'SQL'
-- Conectar a Railway con:
-- mysql -h <RAILWAY_DB_HOST> -u <RAILWAY_DB_USER> -p<PASSWORD> <RAILWAY_DB_NAME>

-- Luego ejecuta:
SELECT 
    'Productos totales' AS métrica,
    COUNT(*) AS valor
FROM products
UNION ALL
SELECT 
    'Movimientos reconteo',
    COUNT(*)
FROM stock_movements 
WHERE notes LIKE '%reconteo%'
UNION ALL
SELECT 
    'Stock total',
    COALESCE(SUM(stock), 0)
FROM products;
SQL;
    
    echo '<div class="code">' . htmlspecialchars($railwayExample) . '</div>';
    
    echo '<p style="margin-top: 15px;"><strong>ℹ️ Nota:</strong> Compara los valores locales (arriba) con los de Railway. Si son diferentes, necesitas sincronizar datos.</p>';
    echo '</div>';
    
    // ============================================================
    // 4. Recomendaciones
    // ============================================================
    echo '<div class="summary">';
    echo '<h2>📋 Checklist para Sincronizar Reconteo</h2>';
    echo '<ul>';
    echo '<li>✓ <strong>Código:</strong> El módulo de reconteo ya está desplegado en Railway (vía Dockerfile)</li>';
    echo '<li>✓ <strong>Acceso:</strong> Verifica que tu usuario en Railway tenga rol "logistica" o "admin"</li>';
    echo '<li>⚠️ <strong>Datos:</strong> Compara productos y stock entre local y Railway (líneas arriba)</li>';
    echo '<li>⚠️ <strong>Si hay diferencias:</strong> Sincroniza la tabla `products` y `stock_movements`</li>';
    echo '<li>✓ <strong>Timezone:</strong> Considera configurar `DB_TIMEZONE=UTC` en Railway si usas zona horaria diferente</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div class="card">';
    echo '<h2>🛠️ Próximos Pasos Sugeridos</h2>';
    echo '<ol>';
    echo '<li><strong>Acceder a Railway:</strong> Ve a tu servicio MySQL en railroad.app → Variables o Connect</li>';
    echo '<li><strong>Copiar credenciales:</strong> DB_HOST, DB_USER, DB_PASSWORD, DB_PORT, DB_NAME</li>';
    echo '<li><strong>Ejecutar query arriba:</strong> Conecta y ejecuta el SQL de comparación</li>';
    echo '<li><strong>Si hay diferencias:</strong> Exporta datos locales e importa a Railway</li>';
    echo '<li><strong>Prueba final:</strong> Intenta hacer un reconteo en Railway y verifica que funcione</li>';
    echo '</ol>';
    echo '</div>';
    
    ?>

</div>
</body>
</html>
