<?php
// views/admin/inventario/low_stock_alerts.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? '';
$headerFile = in_array($role, ['supervisor', 'comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia'], true)
    ? __DIR__ . '/../../employee/partials/header.php'
    : __DIR__ . '/../partials/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alertas de Bajo Stock</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        body { background-color: #1a1a2e; color: #eaeaea; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 16px; }
        .page-header { margin-bottom: 32px; }
        .page-header h1 { color: #00fff0; font-size: 2rem; margin: 0 0 8px 0; }
        .page-header p { color: #a0cfe8; margin: 0; }
        
        .alert-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .stat-card { background: #0f3460; border-radius: 14px; padding: 20px; border: 1px solid rgba(0,255,240,0.12); }
        .stat-card h3 { margin: 0 0 10px 0; color: #a0fdfd; font-size: 0.9rem; text-transform: uppercase; }
        .stat-card p { margin: 0; font-size: 32px; font-weight: 700; color: #00fff0; }
        
        .table-card { background: #16213e; border-radius: 18px; overflow: hidden; box-shadow: 0 0 20px rgba(0,255,240,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f3460; color: #00fff0; padding: 16px 14px; text-align: left; font-weight: 600; }
        td { padding: 16px 14px; border-bottom: 1px solid rgba(255,255,255,.1); }
        tr:hover { background: rgba(0,255,240,0.04); }
        tr:last-child td { border-bottom: none; }
        
        .severity-critical { background: rgba(255,107,107,0.12); color: #ff6b6b; padding: 6px 12px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; display: inline-block; }
        .severity-warning { background: rgba(255,193,7,0.12); color: #ffc107; padding: 6px 12px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; display: inline-block; }
        
        .action-button { padding: 8px 14px; background: rgba(0,255,240,0.12); color: #00fff0; border: 1px solid #00fff0; border-radius: 8px; text-decoration: none; cursor: pointer; font-size: 0.9rem; transition: all 0.3s; }
        .action-button:hover { background: #00fff0; color: #0f172a; }
        
        .stock-bar { display: inline-flex; width: 100%; max-width: 200px; height: 24px; background: #0f172a; border-radius: 12px; overflow: hidden; border: 1px solid rgba(0,255,240,0.2); }
        .stock-fill { background: linear-gradient(90deg, #ff6b6b, #ffc107); height: 100%; transition: width 0.3s; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: white; font-weight: 700; }
        
        .no-alerts { text-align: center; padding: 40px 20px; color: #a0a0a0; }
        .no-alerts p { font-size: 1.1rem; margin: 0; }
        .no-alerts .checkmark { font-size: 48px; margin-bottom: 16px; }
        
        @media(max-width: 860px) {
            th, td { padding: 12px 10px; font-size: 0.95rem; }
        }
    </style>
</head>
<body>
<?php include $headerFile; ?>
<div class="container">
    <div class="page-header">
        <h1>Alertas de Bajo Stock</h1>
        <p>Productos que requieren reabastecimiento inmediato</p>
    </div>

    <?php if (!empty($productosConBajoStock)): ?>
        <div class="alert-stats">
            <div class="stat-card">
                <h3>Total alertas</h3>
                <p><?php echo count($productosConBajoStock); ?></p>
            </div>
            <div class="stat-card">
                <h3>Stock promedio</h3>
                <p><?php echo round(array_sum(array_map(fn($p) => (int)$p->stock, $productosConBajoStock)) / count($productosConBajoStock)); ?> unidades</p>
            </div>
            <div class="stat-card">
                <h3>Críticos (vacíos)</h3>
                <p><?php echo count(array_filter($productosConBajoStock, fn($p) => (int)$p->stock === 0)); ?></p>
            </div>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock actual</th>
                        <th>Stock mínimo</th>
                        <th>Progreso</th>
                        <th>Severidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productosConBajoStock as $prod): ?>
                        <?php 
                            $stockActual = (int)$prod->stock;
                            $stockMin = (int)$prod->stock_minimum;
                            $porcentaje = $stockMin > 0 ? (($stockActual / $stockMin) * 100) : 0;
                            $isCritical = $stockActual === 0;
                            $severidad = $isCritical ? 'critical' : 'warning';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($prod->name); ?></strong></td>
                            <td><?php echo $stockActual; ?> unidades</td>
                            <td><?php echo $stockMin; ?> unidades</td>
                            <td>
                                <div class="stock-bar">
                                    <div class="stock-fill" style="width: <?php echo min($porcentaje, 100); ?>%;">
                                        <?php echo round($porcentaje); ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($isCritical): ?>
                                    <span class="severity-critical">CRÍTICO</span>
                                <?php else: ?>
                                    <span class="severity-warning">ALERTA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=editProductForm&id=<?php echo $prod->id; ?>" class="action-button">Editar</a>
                                <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=addInventoryMovementForm&product_id=<?php echo $prod->id; ?>" class="action-button" style="margin-left: 8px;">Reabastecimiento</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-alerts">
            <div class="checkmark">✓</div>
            <p>¡Excelente! Todos los productos están en niveles de stock adecuados.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Chatbot Widget -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
<script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
