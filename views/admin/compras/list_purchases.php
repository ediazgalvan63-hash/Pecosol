<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compras</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body { background:#1a1a2e; color:#eaeaea; }
        .container { max-width: 1160px; margin: 32px auto; padding: 0 16px; }
        .toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .toolbar h1 { margin:0; color:#00fff0; }
        .table-wrap {
            background:#16213e;
            border:1px solid rgba(0,255,240,0.16);
            border-radius:14px;
            overflow:auto;
            box-shadow: 0 10px 24px rgba(0,0,0,0.25);
        }
        table { width:100%; border-collapse:collapse; min-width:900px; }
        thead th {
            background:#0f3460;
            color:#a0fdfd;
            font-weight:700;
            letter-spacing:0.02em;
            padding:14px 12px;
            text-align:left;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        tbody td {
            padding:12px;
            border-top:1px solid rgba(255,255,255,0.08);
            color:#e9f9ff;
            vertical-align:top;
        }
        tbody tr:nth-child(even) { background: rgba(255,255,255,0.02); }
        tbody tr:hover { background: rgba(0,255,240,0.06); }
        .qty-badge {
            display:inline-block;
            padding:4px 10px;
            border-radius:999px;
            font-weight:700;
            color:#0c2f2f;
            background:#6df7db;
        }
        .muted { color:#a6c6d8; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container" style="max-width: 1100px; margin: 32px auto;">
    <div class="toolbar">
        <h1>Compras / Abastecimiento</h1>
        <a class="button" href="<?php echo BASE_URL; ?>index.php?controller=admin&action=addPurchaseForm">Nueva compra</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Proveedor</th>
                    <th>Registrado por</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($compras)): ?>
                    <?php foreach ($compras as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c->purchase_date); ?></td>
                            <td><?php echo htmlspecialchars($c->product_name); ?></td>
                            <td><span class="qty-badge">+<?php echo (int)$c->quantity; ?></span></td>
                            <td><?php echo htmlspecialchars($c->supplier); ?></td>
                            <td><?php echo htmlspecialchars($c->user_name); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($c->notes ?? 'Sin observaciones'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="muted">No hay compras registradas todavía.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
