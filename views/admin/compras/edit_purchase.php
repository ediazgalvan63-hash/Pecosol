<?php
// views/admin/compras/edit_purchase.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Compra</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
      body {
        background-color: #1a1a2e;
        background-image: url('<?php echo BASE_URL; ?>assets/img/overlapping-circles.svg');
        background-repeat: repeat;
        background-size: 60px;
        background-attachment: fixed;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #eaeaea;
        margin: 0;
        padding: 0;
      }

      .container {
        max-width: 920px;
        margin: 38px auto;
        padding: 0 16px;
      }

      .page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
        flex-wrap: wrap;
      }

      .page-head h1 {
        margin: 0;
        color: #60a5fa;
        font-size: 2rem;
        line-height: 1.15;
      }

      .page-head p {
        margin: 8px 0 0;
        color: #a0cfe8;
        max-width: 680px;
      }

      .form-shell {
        background: rgba(22, 33, 62, 0.94);
        border: 1px solid rgba(96, 165, 250, 0.18);
        border-radius: 16px;
        box-shadow: 0 12px 34px rgba(0,0,0,0.35);
        overflow: hidden;
      }

      .form-body {
        padding: 18px;
      }

      .grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 14px;
      }

      @media (max-width: 860px) {
        .grid { grid-template-columns: 1fr; }
      }

      label {
        display: block;
        margin: 0 0 6px;
        color: #cfefff;
        font-weight: 600;
        letter-spacing: 0.01em;
      }

      select, input, textarea {
        width: 100%;
        background: #0f172a;
        border: 1px solid rgba(96, 165, 250, 0.22);
        color: #eaeaea;
        border-radius: 12px;
        padding: 12px 12px;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease, transform .05s ease;
        box-sizing: border-box;
      }

      textarea { resize: vertical; min-height: 92px; }

      select:focus, input:focus, textarea:focus {
        border-color: rgba(96, 165, 250, 0.55);
        box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.10);
      }

      .hint {
        margin-top: 8px;
        color: #9ae7ff;
        font-size: 0.9rem;
      }

      .error {
        background: rgba(255, 75, 75, 0.12);
        border: 1px solid rgba(255, 75, 75, 0.3);
        color: #ffb3b3;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 16px;
      }

      .actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        flex-wrap: wrap;
      }

      .btn {
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
      }

      .btn-primary {
        background: #60a5fa;
        color: #0f172a;
      }

      .btn-primary:hover {
        background: #00e6d6;
        transform: translateY(-1px);
      }

      .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #60a5fa;
        border: 1px solid rgba(255,255,255,0.2);
      }

      .btn-secondary:hover {
        background: rgba(255,255,255,0.15);
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
<div class="container">
    <div class="page-head">
        <div>
            <h1>Editar Compra</h1>
            <p>Modifica los detalles de la compra seleccionada. Los cambios afectarán el stock y el kardex.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=updatePurchase">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($compra->id); ?>">
        <div class="form-shell">
            <div class="form-body">
                <div class="grid">
                    <div>
                        <label for="product_id">Producto *</label>
                        <select name="product_id" id="product_id" required>
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?php echo $p->id; ?>" <?php echo ($p->id == $compra->product_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p->name); ?> (Stock: <?php echo $p->stock; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="hint">Elige el producto comprado.</div>
                    </div>
                    <div>
                        <label for="quantity">Cantidad *</label>
                        <input type="number" name="quantity" id="quantity" value="<?php echo htmlspecialchars($compra->quantity); ?>" min="1" required>
                        <div class="hint">Unidades adquiridas.</div>
                    </div>
                    <div>
                        <label for="price">Precio Unitario</label>
                        <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($compra->price ?? 0); ?>" min="0" step="0.01">
                        <div class="hint">Costo por unidad.</div>
                    </div>
                </div>
                <div style="margin-top: 16px;">
                    <label for="supplier">Proveedor *</label>
                    <input type="text" name="supplier" id="supplier" value="<?php echo htmlspecialchars($compra->supplier); ?>" required>
                    <div class="hint">Nombre del proveedor o distribuidor.</div>
                </div>
                <div style="margin-top: 16px;">
                    <label for="notes">Notas (opcional)</label>
                    <textarea name="notes" id="notes"><?php echo htmlspecialchars($compra->notes ?? ''); ?></textarea>
                    <div class="hint">Observaciones adicionales sobre la compra.</div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary">Actualizar Compra</button>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listPurchases" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>