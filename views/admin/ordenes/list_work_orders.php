<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Órdenes de Trabajo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body { background:#1a1a2e; color:#eaeaea; }
        .container { max-width: 1200px; margin: 32px auto; padding: 0 16px; }
        .toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .toolbar h1 { margin:0; color:#00fff0; }
        .table-wrap {
            background:#16213e;
            border:1px solid rgba(0,255,240,0.16);
            border-radius:14px;
            overflow:auto;
            box-shadow: 0 10px 24px rgba(0,0,0,0.25);
        }
        table { width:100%; border-collapse:collapse; min-width:1080px; }
        thead th {
            background:#0f3460;
            color:#a0fdfd;
            font-weight:700;
            padding:14px 12px;
            text-align:left;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        tbody td {
            padding:12px;
            border-top:1px solid rgba(255,255,255,0.08);
            vertical-align:top;
        }
        tbody tr:nth-child(even) { background: rgba(255,255,255,0.02); }
        tbody tr:hover { background: rgba(0,255,240,0.06); }
        .status {
            display:inline-block;
            padding:4px 10px;
            border-radius:999px;
            font-weight:700;
            font-size:0.83rem;
        }
        .status.pendiente { background:#3a2f12; color:#ffe08a; }
        .status.en_proceso { background:#12384a; color:#88dcff; }
        .status.finalizado { background:#15392d; color:#9af7c8; }
        .venta-tag {
            display:inline-block;
            padding:3px 9px;
            border-radius:999px;
            background:#253055;
            color:#d2deff;
            font-weight:600;
        }
        .update-form { display:flex; gap:8px; align-items:center; }
        .update-form select {
            background:#111b38;
            color:#eaf7ff;
            border:1px solid rgba(0,255,240,0.25);
            border-radius:8px;
            padding:8px;
        }
        .muted { color:#a6c6d8; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container" style="max-width: 1150px; margin: 32px auto;">
    <div class="toolbar">
        <h1>Órdenes de Trabajo</h1>
        <a class="button" href="<?php echo BASE_URL; ?>index.php?controller=admin&action=addWorkOrderForm">Nueva orden</a>
    </div>

    <?php if (!empty($_SESSION['error_work_order'])): ?>
        <div class="error"><?php echo htmlspecialchars($_SESSION['error_work_order']); unset($_SESSION['error_work_order']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success_work_order'])): ?>
        <div style="background:#173f2f;color:#9af7c8;border:1px solid #2fa86d;padding:10px;border-radius:8px;">
            <?php echo htmlspecialchars($_SESSION['success_work_order']); unset($_SESSION['success_work_order']); ?>
        </div>
    <?php endif; ?>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Técnico</th>
                    <th>Materiales</th>
                    <th>Estado</th>
                    <th>Venta</th>
                    <th>Actualizar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ordenes)): ?>
                    <?php foreach ($ordenes as $o): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($o->created_at); ?></td>
                            <td><?php echo htmlspecialchars($o->client_name); ?></td>
                            <td><?php echo htmlspecialchars($o->service_type); ?></td>
                            <td><?php echo htmlspecialchars($o->technician_name); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($o->materials_used ?? 'Sin materiales registrados'); ?></td>
                            <td>
                                <span class="status <?php echo htmlspecialchars($o->status); ?>">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $o->status)); ?>
                                </span>
                            </td>
                            <td><?php echo $o->sale_id ? ('<span class="venta-tag">#' . (int)$o->sale_id . '</span>') : '<span class="muted">-</span>'; ?></td>
                            <td>
                                <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=updateWorkOrderStatus" class="update-form">
                                    <input type="hidden" name="id" value="<?php echo (int)$o->id; ?>">
                                    <select name="status">
                                        <option value="pendiente" <?php echo $o->status === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="en_proceso" <?php echo $o->status === 'en_proceso' ? 'selected' : ''; ?>>En proceso</option>
                                        <option value="finalizado" <?php echo $o->status === 'finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                    </select>
                                    <button class="button" type="submit">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="muted">No hay órdenes registradas todavía.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
