<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reconteo de Inventario</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body { background:#1a1a2e; color:#eaeaea; }
        .container { max-width: 1120px; margin: 32px auto; padding: 0 16px; }
        .grid { display:grid; grid-template-columns: 360px 1fr; gap:16px; align-items:start; }
        .form-card, .table-card {
            background:#16213e;
            border:1px solid rgba(0,255,240,0.16);
            border-radius:14px;
            padding:16px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.25);
        }
        label { color:#a0fdfd; font-weight:600; display:block; margin-bottom:6px; }
        select, input {
            width:100%;
            background:#111b38;
            color:#eaf7ff;
            border:1px solid rgba(0,255,240,0.25);
            border-radius:8px;
            padding:10px;
            margin-bottom:10px;
        }
        .table-wrap { overflow:auto; border-radius:10px; }
        table { width:100%; border-collapse:collapse; min-width:640px; }
        thead th { background:#0f3460; color:#a0fdfd; padding:12px; text-align:left; }
        tbody td { padding:11px 12px; border-top:1px solid rgba(255,255,255,0.08); }
        tbody tr:nth-child(even) { background: rgba(255,255,255,0.02); }
        tbody tr:hover { background: rgba(0,255,240,0.06); }
        .state-ok { color:#9af7c8; font-weight:700; }
        .state-low { color:#ff9ea4; font-weight:700; }
        @media (max-width: 960px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php
$role = $_SESSION['role'] ?? '';
$useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia', 'supervisor'], true);
if ($useEmployeeHeader) {
    include __DIR__ . '/../../employee/partials/header.php';
} else {
    include __DIR__ . '/../partials/header.php';
}
?>
<div class="container">
    <h1>Reconteo de Inventario</h1>
    <p>Compara stock de sistema vs stock físico y ajusta automáticamente.</p>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div style="background:#173f2f;color:#9af7c8;border:1px solid #2fa86d;padding:10px;border-radius:8px;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=processInventoryRecount" class="form-card">
            <label for="product_id">Producto *</label>
            <select id="product_id" name="product_id" required>
                <option value="">Selecciona un producto</option>
                <?php foreach ($productos as $prod): ?>
                    <option value="<?php echo (int)$prod->id; ?>">
                        <?php echo htmlspecialchars($prod->name); ?> (Sistema: <?php echo (int)$prod->stock; ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="physical_stock">Stock físico *</label>
            <input type="number" id="physical_stock" name="physical_stock" min="0" required>

            <button type="submit" class="button">Ajustar inventario</button>
        </form>

        <div class="table-card">
            <h3 style="margin-top:0;color:#00fff0;">Tabla de apoyo para reconteo</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock sistema</th>
                            <th>Stock mínimo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <?php $isLow = (int)$prod->stock <= (int)$prod->stock_minimum; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod->name); ?></td>
                                <td><?php echo (int)$prod->stock; ?></td>
                                <td><?php echo (int)$prod->stock_minimum; ?></td>
                                <td class="<?php echo $isLow ? 'state-low' : 'state-ok'; ?>">
                                    <?php echo $isLow ? 'Bajo stock' : 'Normal'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
