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
<?php
$role = $_SESSION['role'] ?? '';
$useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia'], true);
if ($useEmployeeHeader) {
    include __DIR__ . '/../../employee/partials/header.php';
} else {
    include __DIR__ . '/../partials/header.php';
}
$reportsAction = $reportsAction ?? 'reports';
$reportsController = ($dashboardMode ?? false) ? 'dashboard' : 'admin';
$reportUrlBase = BASE_URL . 'index.php?controller=' . $reportsController . '&action=' . $reportsAction;
?>
<div class="container">
    <h1 class="header-title">Reportes Exportables</h1>

    <div class="section-card">
        <h2>Inventario Actual</h2>
        <p class="help-text">Descarga un archivo XLSX con el estado actual de todos los productos, sus niveles de stock y alertas de bajo stock.</p>
        <a class="button" href="<?php echo BASE_URL; ?>index.php?controller=admin&action=exportCurrentInventoryCsv">Descargar XLSX</a>
    </div>

    <div class="section-card">
        <h2>Movimientos / Kardex</h2>
        <p class="help-text">Aplica filtros por rango de fechas, producto y tipo de movimiento antes de exportar a XLSX.</p>
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
                <button type="submit" class="button">Exportar XLSX</button>
                <button type="reset" onclick="window.location.href='<?php echo htmlspecialchars($reportUrlBase); ?>'" class="button">Limpiar filtros</button>
            </div>
        </form>
    </div>

    <div class="section-card">
        <h2>Ventas</h2>
        <p class="help-text">Filtra las ventas por rango de fechas para obtener un informe XLSX coherente con la trazabilidad del inventario.</p>
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
                <button type="submit" class="button">Exportar XLSX</button>
                <button type="reset" onclick="window.location.href='<?php echo htmlspecialchars($reportUrlBase); ?>'" class="button">Limpiar filtros</button>
            </div>
        </form>
    </div>

    <div class="section-card">
        <h2>Bitácora de Auditoría (Trazabilidad)</h2>
        <p class="help-text">Registro de operaciones críticas para sustento técnico: ventas, compras, ajustes y órdenes de trabajo.</p>
        <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
            <table style="width:100%; border-collapse: collapse; font-size: 14px;">
                <thead style="position: sticky; top: 0; background: #16213e; z-index: 1;">
                    <tr>
                        <th style="padding: 12px 8px; border-bottom: 2px solid #00fff0; color: #00fff0; text-align: left; font-weight: 600;">Fecha</th>
                        <th style="padding: 12px 8px; border-bottom: 2px solid #00fff0; color: #00fff0; text-align: left; font-weight: 600;">Usuario</th>
                        <th style="padding: 12px 8px; border-bottom: 2px solid #00fff0; color: #00fff0; text-align: left; font-weight: 600;">Acción</th>
                        <th style="padding: 12px 8px; border-bottom: 2px solid #00fff0; color: #00fff0; text-align: left; font-weight: 600;">Entidad</th>
                        <th style="padding: 12px 8px; border-bottom: 2px solid #00fff0; color: #00fff0; text-align: left; font-weight: 600;">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($auditorias)): ?>
                        <?php $rowCount = 0; ?>
                        <?php foreach ($auditorias as $a): ?>
                            <?php $rowCount++; ?>
                            <tr style="background-color: <?php echo $rowCount % 2 === 0 ? '#1a1a2e' : '#16213e'; ?>; transition: background-color 0.2s;">
                                <td style="padding: 10px 8px; border-bottom: 1px solid #2a3b63; color: #eaeaea;"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($a->created_at))); ?></td>
                                <td style="padding: 10px 8px; border-bottom: 1px solid #2a3b63; color: #eaeaea;"><?php echo htmlspecialchars($a->user_name ?? 'Sistema'); ?></td>
                                <td style="padding: 10px 8px; border-bottom: 1px solid #2a3b63;">
                                    <span style="display: inline-flex; align-items: center; gap: 6px; color: <?php echo $a->action === 'create' ? '#4ade80' : ($a->action === 'update' ? '#fbbf24' : ($a->action === 'delete' ? '#ff6b6b' : '#a0a0a0')); ?>; font-weight: 500;">
                                        <?php 
                                        $icon = $a->action === 'create' ? '➕' : ($a->action === 'update' ? '✏️' : ($a->action === 'delete' ? '🗑️' : 'ℹ️'));
                                        echo $icon . ' ' . htmlspecialchars(strtoupper($a->action));
                                        ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 8px; border-bottom: 1px solid #2a3b63; color: #eaeaea;"><?php echo htmlspecialchars(ucfirst($a->entity)); ?></td>
                                <td style="padding: 10px 8px; border-bottom: 1px solid #2a3b63; color: #a0a0a0; max-width: 300px; word-wrap: break-word;"><?php echo htmlspecialchars($a->details ?? 'Sin detalles'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: #a0a0a0; font-style: italic;">Sin eventos de auditoría aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
