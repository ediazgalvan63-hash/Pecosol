<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        body { background:#1a1a2e; color:#eaeaea; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 16px; }
        .header-title { margin-bottom: 30px; text-align: center; color: #00fff0; }
        .section-card { background:#16213e; border-radius: 18px; padding:24px; margin-bottom: 24px; box-shadow: 0 0 18px rgba(0,255,240,0.08); }
        .section-card h2 { margin-top: 0; color: #a0fdfd; }
        .field-row { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; margin-bottom: 16px; }
        .field-row label { display: block; font-size: 0.95rem; color: #e3f7ff; margin-bottom: 6px; }
        .field-row input,
        .field-row select { width: 220px; padding: 10px 12px; border-radius: 10px; border: 1px solid #0f3460; background: rgba(0,255,240,0.06); color: #eaeaea; }
        .field-row input[type="date"] { max-width: 220px; }
        .field-row button { margin-top: 4px; }
        .help-text { color: #a0cfe8; margin-bottom: 18px; }
        .form-actions { display: flex; flex-wrap: wrap; gap: 14px; }
        .note { color: #e0e0e0; font-size: 0.95rem; }
        @media(max-width: 820px) {
            .field-row { flex-direction: column; align-items: stretch; }
            .field-row input, .field-row select { width: 100%; }
            .form-actions { flex-direction: column; width: 100%; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <h1 class="header-title">Reportes Exportables</h1>

    <div class="section-card">
        <h2>Inventario Actual</h2>
        <p class="help-text">Descarga un archivo Excel (.xlsx) con el estado actual de todos los productos, sus niveles de stock y alertas de bajo stock.</p>
        <a class="button" href="<?php echo BASE_URL; ?>index.php?controller=admin&action=exportCurrentInventoryCsv">Descargar Inventario Actual</a>
    </div>

    <div class="section-card">
        <h2>Movimientos / Kardex</h2>
        <p class="help-text">Aplica filtros por rango de fechas, producto y tipo de movimiento antes de exportar a Excel.</p>
        <form method="get" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=exportMovementsCsv">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="exportMovementsCsv">
            <div class="field-row">
                <div>
                    <label for="start_date_mov">Fecha desde</label>
                    <input type="date" id="start_date_mov" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                </div>
                <div>
                    <label for="end_date_mov">Fecha hasta</label>
                    <input type="date" id="end_date_mov" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                </div>
                <div>
                    <label for="product_id_mov">Producto</label>
                    <select id="product_id_mov" name="product_id">
                        <option value="">Todos los productos</option>
                        <?php foreach ($productos as $prod): ?>
                            <option value="<?php echo $prod->id; ?>" <?php echo (isset($_GET['product_id']) && $_GET['product_id'] == $prod->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($prod->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="movement_type">Tipo de movimiento</label>
                    <select id="movement_type" name="movement_type">
                        <option value="">Todos</option>
                        <option value="ingreso" <?php echo (isset($_GET['movement_type']) && $_GET['movement_type'] === 'ingreso') ? 'selected' : ''; ?>>Ingreso</option>
                        <option value="salida" <?php echo (isset($_GET['movement_type']) && $_GET['movement_type'] === 'salida') ? 'selected' : ''; ?>>Salida</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Exportar Movimientos</button>
                <button type="reset" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=admin&action=reports'" class="button">Limpiar filtros</button>
            </div>
        </form>
    </div>

    <div class="section-card">
        <h2>Ventas</h2>
        <p class="help-text">Filtra las ventas por rango de fechas para obtener un informe Excel coherente con la trazabilidad del inventario.</p>
        <form method="get" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=exportSalesCsv">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="exportSalesCsv">
            <div class="field-row">
                <div>
                    <label for="start_date_sales">Fecha desde</label>
                    <input type="date" id="start_date_sales" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                </div>
                <div>
                    <label for="end_date_sales">Fecha hasta</label>
                    <input type="date" id="end_date_sales" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Exportar Ventas</button>
                <button type="reset" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=admin&action=reports'" class="button">Limpiar filtros</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
