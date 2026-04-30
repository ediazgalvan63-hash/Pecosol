<?php
$productIdPreselected = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Movimiento de Inventario</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        body { background-color: #1a1a2e; color: #eaeaea; }
        .container { max-width: 640px; margin: 40px auto; padding: 0 16px; }
        .page-header { margin-bottom: 28px; }
        .page-header h1 { color: #00fff0; margin: 0 0 8px 0; font-size: 1.8rem; }
        .page-header p { color: #a0cfe8; margin: 0; }
        .form-card { background: #16213e; border-radius: 18px; padding: 28px; box-shadow: 0 0 20px rgba(0,255,240,0.08); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #a0fdfd; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid #0f3460; background: rgba(0,255,240,0.06); color: #eaeaea; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #00fff0; box-shadow: 0 0 12px rgba(0,255,240,0.3); }
        .form-group select { -webkit-appearance: none; appearance: none; }
        .form-group select::-ms-expand { display: none; }
        .form-group select option { background: #0f3460; color: #eaeaea; padding: 6px; }
        .form-group textarea { resize: vertical; }
        .error { background: rgba(255,107,107,0.12); color: #ff8b8b; border: 1px solid #ff6b6b; padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; }
        .help-text { color: #9ae7ff; font-size: 0.9rem; margin-top: 6px; }
        .form-actions { display: flex; gap: 12px; margin-top: 28px; }
        .form-actions .button { flex: 1; }
        @media(max-width: 640px) {
            .form-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1>Registrar Movimiento</h1>
        <p>Entrada o salida de inventario</p>
    </div>

    <div class="form-card">
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=storeInventoryMovement">
            <div class="form-group">
                <label for="product_id">Producto *</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Seleccionar producto...</option>
                    <?php foreach ($productos as $prod): ?>
                        <option value="<?php echo (int)$prod->id; ?>" <?php echo $productIdPreselected === (int)$prod->id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prod->name); ?> (Stock: <?php echo (int)$prod->stock; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="help-text">Selecciona el producto para registrar su movimiento</small>
            </div>

            <div class="form-group">
                <label for="movement_type">Tipo de movimiento *</label>
                <select id="movement_type" name="movement_type" required>
                    <option value="ingreso">Entrada (Entrada de stock)</option>
                    <option value="salida">Salida (Salida de stock)</option>
                </select>
                <small class="help-text">Indica si es una entrada o salida de inventario</small>
            </div>

            <div class="form-group">
                <label for="quantity">Cantidad *</label>
                <input type="number" id="quantity" name="quantity" min="1" step="1" placeholder="Ej: 50" required>
                <small class="help-text">Número de unidades a registrar</small>
            </div>

            <div class="form-group">
                <label for="reason">Motivo / Descripción</label>
                <textarea id="reason" name="reason" rows="4" placeholder="Compra, ajuste de inventario, merma, devolución de cliente, etc."></textarea>
                <small class="help-text">Opcional: Describe el motivo del movimiento para trazabilidad</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="button">Guardar Movimiento</button>
                <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listInventoryMovements" class="button" style="text-align: center; text-decoration: none;">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<!-- Chatbot Widget -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
<script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
