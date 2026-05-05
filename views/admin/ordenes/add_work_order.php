<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Orden de Trabajo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container" style="max-width: 760px; margin: 32px auto;">
    <h1>Nueva Orden de Trabajo</h1>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=storeWorkOrder" class="form-card">
        <label>Cliente *</label>
        <input type="text" name="client_name" required style="width:100%;margin-bottom:10px;">

        <label>Tipo de servicio *</label>
        <input type="text" name="service_type" required style="width:100%;margin-bottom:10px;">

        <label>Técnico *</label>
        <input type="text" name="technician_name" required style="width:100%;margin-bottom:10px;">

        <label>Materiales utilizados</label>
        <textarea name="materials_used" rows="3" style="width:100%;margin-bottom:10px;"></textarea>

        <label>Estado *</label>
        <select name="status" required style="width:100%;margin-bottom:10px;">
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En proceso</option>
            <option value="finalizado">Finalizado</option>
        </select>

        <label>Vincular a venta (opcional)</label>
        <select name="sale_id" style="width:100%;margin-bottom:10px;">
            <option value="">Sin vincular</option>
            <?php foreach ($ventas as $v): ?>
                <option value="<?php echo (int)$v->id; ?>">
                    Venta #<?php echo (int)$v->id; ?> - <?php echo htmlspecialchars($v->product_name); ?> - S/. <?php echo number_format((float)$v->total_price, 2); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Observaciones</label>
        <textarea name="notes" rows="3" style="width:100%;margin-bottom:10px;"></textarea>

        <button type="submit" class="button">Registrar orden</button>
    </form>
</div>
</body>
</html>
