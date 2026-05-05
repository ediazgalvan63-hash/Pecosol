<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Compra</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container" style="max-width: 720px; margin: 32px auto;">
    <h1>Registrar Compra</h1>
    <p>Genera ingreso automático en Kardex y actualiza stock.</p>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=storePurchase" class="form-card">
        <label for="product_id">Producto *</label>
        <select id="product_id" name="product_id" required style="width:100%;margin-bottom:12px;">
            <option value="">Selecciona un producto</option>
            <?php foreach ($productos as $prod): ?>
                <option value="<?php echo (int)$prod->id; ?>">
                    <?php echo htmlspecialchars($prod->name); ?> (Stock actual: <?php echo (int)$prod->stock; ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="quantity">Cantidad *</label>
        <input type="number" id="quantity" name="quantity" min="1" required style="width:100%;margin-bottom:12px;">

        <label for="supplier">Proveedor *</label>
        <input type="text" id="supplier" name="supplier" maxlength="120" required style="width:100%;margin-bottom:12px;">

        <label for="notes">Notas</label>
        <textarea id="notes" name="notes" rows="3" style="width:100%;margin-bottom:12px;"></textarea>

        <button type="submit" class="button">Registrar compra</button>
    </form>
</div>
</body>
</html>
