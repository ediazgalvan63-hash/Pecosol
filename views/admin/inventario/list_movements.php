<?php
$startDate = htmlspecialchars($_GET['start_date'] ?? '');
$endDate = htmlspecialchars($_GET['end_date'] ?? '');
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$movementType = htmlspecialchars($_GET['movement_type'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos de Inventario</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        body { background-color: #1a1a2e; color: #eaeaea; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 16px; }
        .page-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; }
        .page-header h1 { font-size: 2rem; color: #00fff0; margin: 0; }
        .filter-card, .table-card { background: #16213e; border-radius: 18px; padding: 24px; box-shadow: 0 0 20px rgba(0,255,240,0.08); margin-bottom: 24px; }
        .filter-row { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; }
        .filter-field { display: flex; flex-direction: column; flex: 1 1 220px; min-width: 180px; }
        .filter-field label { color: #a0fdfd; margin-bottom: 8px; font-weight: 600; }
        .filter-field input,
        .filter-field select {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #0f3460;
            background: rgba(0,255,240,0.06);
            color: #eaeaea;
        }
        .filter-field select {
            background: rgba(0,255,240,0.12);
            color: #eaeaea;
            -webkit-appearance: none;
            appearance: none;
        }
        .filter-field select::-ms-expand {
            display: none;
        }
        .filter-field select option {
            background: #0f3460;
            color: #eaeaea;
        }
        .filter-actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
        .filter-actions .button { min-width: 170px; }
        table { width: 100%; border-collapse: collapse; background: transparent; }
        th, td { padding: 14px 12px; border-bottom: 1px solid rgba(255,255,255,.1); }
        th { background: #0f3460; color: #00fff0; position: sticky; top: 0; }
        tbody tr:hover { background: rgba(0,255,240,0.06); }
        .status-chip { display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; }
        .ingreso { background: rgba(125,255,125,0.12); color: #7dff7d; }
        .salida { background: rgba(255,139,139,0.12); color: #ff8b8b; }
        .no-data { color: #a0a0a0; font-style: italic; }
        @media(max-width: 860px) {
            .filter-row { flex-direction: column; }
            th, td { padding: 12px 10px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1>Historial de Movimientos (Kardex)</h1>
        <a class="button" href="<?php echo BASE_URL; ?>index.php?controller=admin&action=addInventoryMovementForm">Registrar Movimiento</a>
    </div>

    <div class="filter-card">
        <h2 style="color:#eaeaea; margin-top:0;">Filtros de Kardex</h2>
        <div class="filter-row">
            <div class="filter-field">
                <label for="start_date">Fecha desde</label>
                <input type="date" id="start_date" name="start_date" form="filterForm" value="<?php echo $startDate; ?>">
            </div>
            <div class="filter-field">
                <label for="end_date">Fecha hasta</label>
                <input type="date" id="end_date" name="end_date" form="filterForm" value="<?php echo $endDate; ?>">
            </div>
            <div class="filter-field">
                <label for="product_id">Producto</label>
                <select id="product_id" name="product_id" form="filterForm">
                    <option value="0">Todos los productos</option>
                    <?php foreach ($productos as $prod): ?>
                        <option value="<?php echo $prod->id; ?>" <?php echo $productId === (int)$prod->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($prod->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-field">
                <label for="movement_type">Tipo de movimiento</label>
                <select id="movement_type" name="movement_type" form="filterForm">
                    <option value="">Todos</option>
                    <option value="ingreso" <?php echo $movementType === 'ingreso' ? 'selected' : ''; ?>>Ingreso</option>
                    <option value="salida" <?php echo $movementType === 'salida' ? 'selected' : ''; ?>>Salida</option>
                </select>
            </div>
        </div>
        <div class="filter-actions">
            <form id="filterForm" method="get" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=listInventoryMovements" style="display:inline-flex; width: 100%;">
                <input type="hidden" name="controller" value="admin">
                <input type="hidden" name="action" value="listInventoryMovements">
                <button type="submit" class="button">Aplicar filtros</button>
            </form>
            <a class="button" href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listInventoryMovements">Restablecer filtros</a>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Usuario</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($movimientos)): ?>
                <?php foreach ($movimientos as $mov): ?>
                    <tr>
                        <td><?php echo (int)$mov->id; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($mov->movement_date)); ?></td>
                        <td><span class="status-chip <?php echo $mov->movement_type === 'ingreso' ? 'ingreso' : 'salida'; ?>"><?php echo strtoupper($mov->movement_type); ?></span></td>
                        <td><?php echo htmlspecialchars($mov->product_name); ?></td>
                        <td><?php echo abs((int)$mov->quantity_change); ?></td>
                        <td><?php echo htmlspecialchars($mov->user_name); ?></td>
                        <td><?php echo htmlspecialchars($mov->notes ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="no-data">No hay movimientos que coincidan con los filtros seleccionados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
