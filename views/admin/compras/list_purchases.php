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
        .btn-edit, .btn-delete {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 8px;
        }
        .btn-edit {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-edit:hover {
            background: #1d4ed8;
        }
        .btn-delete {
            background: #dc2626;
            color: #ffffff;
        }
        .btn-delete:hover {
            background: #b91c1c;
        }
        .btn-add-large {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(96, 165, 250, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(96, 165, 250, 0.5);
        }
        .btn-add-large:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(96, 165, 250, 0.4);
        }
        .btn-icon {
            font-size: 1.2rem;
            font-weight: bold;
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
?>
<div class="container" style="max-width: 1100px; margin: 32px auto;">
    <div class="toolbar">
        <h1>Compras / Abastecimiento</h1>
        <?php if (in_array($role, ['admin', 'logistica'])): ?>
        <a href="index.php?controller=admin&action=addPurchaseForm" class="btn-add-large">
            <span class="btn-icon">+</span>
            Agregar Compra
        </a>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Precio Total</th>
                    <th>Proveedor</th>
                    <th>Registrado por</th>
                    <th>Notas</th>
                    <?php if (in_array($role, ['admin', 'logistica'])): ?>
                    <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $totalGeneral = 0; ?>
                <?php if (!empty($compras)): ?>
                    <?php foreach ($compras as $c): ?>
                        <?php $totalCompra = (float)$c->price * (int)$c->quantity; $totalGeneral += $totalCompra; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c->purchase_date); ?></td>
                            <td><?php echo htmlspecialchars($c->product_name); ?></td>
                            <td><span class="qty-badge">+<?php echo (int)$c->quantity; ?></span></td>
                            <td><?php echo number_format($c->price, 2); ?> Bs</td>
                            <td><?php echo number_format($totalCompra, 2); ?> Bs</td>
                            <td><?php echo htmlspecialchars($c->supplier); ?></td>
                            <td><?php echo htmlspecialchars($c->user_name); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($c->notes ?? 'Sin observaciones'); ?></td>
                            <?php if (in_array($role, ['admin', 'logistica'])): ?>
                            <td>
                                <a href="index.php?controller=admin&action=editPurchaseForm&id=<?php echo $c->id; ?>" class="btn-edit">Editar</a>
                                <a href="index.php?controller=admin&action=deletePurchase&id=<?php echo $c->id; ?>" class="btn-delete" onclick="return confirm('¿Eliminar esta compra?')">Eliminar</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: rgba(0,255,240,0.08); font-weight:700;">
                        <td colspan="4">Total general</td>
                        <td><?php echo number_format($totalGeneral, 2); ?> Bs</td>
                        <td colspan="<?php echo in_array($role, ['admin', 'logistica']) ? 4 : 3; ?>"></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo in_array($role, ['admin', 'logistica']) ? 8 : 7; ?>" class="muted">No hay compras registradas todavía.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
