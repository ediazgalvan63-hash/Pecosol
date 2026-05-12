<?php
$role = $_SESSION['role'] ?? '';
$useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia'], true);
if ($useEmployeeHeader) {
    include __DIR__ . '/../../employee/partials/header.php';
} else {
    include __DIR__ . '/../partials/header.php';
}
?>
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
        font-size: 0.92rem;
      }

      .error {
        margin: 14px 0;
        background: rgba(255, 75, 75, 0.14);
        border: 1px solid rgba(255, 107, 107, 0.55);
        color: #ffb1b1;
        padding: 10px 12px;
        border-radius: 12px;
      }

      .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 18px;
        border-top: 1px solid rgba(255,255,255,0.08);
        background: rgba(15, 23, 42, 0.55);
      }

      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 11px 16px;
        border-radius: 999px;
        border: 1px solid rgba(0,255,240,0.25);
        text-decoration: none;
        cursor: pointer;
        font-weight: 800;
        letter-spacing: 0.02em;
        transition: transform .12s ease, box-shadow .12s ease, background-color .12s ease;
      }

    </style>
<div class="container">
    <div class="page-head">
      <div>
        <h1>Registrar compra</h1>
        <p>Esta compra genera un <strong>ingreso automático en Kardex</strong> y actualiza el stock del producto seleccionado.</p>
      </div>
      <div>
        <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php?controller=<?php echo $useEmployeeHeader ? 'dashboard&action=logisticsPurchases' : 'admin&action=listPurchases'; ?>">← Volver</a>
      </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=storePurchase" class="form-shell">
      <div class="form-body">
        <div class="grid">
          <div>
            <label for="product_id">Producto *</label>
            <select id="product_id" name="product_id" required>
                <option value="">Selecciona un producto</option>
                <?php foreach ($productos as $prod): ?>
                    <option value="<?php echo (int)$prod->id; ?>">
                        <?php echo htmlspecialchars($prod->name); ?> (Stock actual: <?php echo (int)$prod->stock; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="hint">Tip: el stock se actualiza automáticamente al registrar.</div>
          </div>

          <div>
            <label for="quantity">Cantidad *</label>
            <input type="number" id="quantity" name="quantity" min="1" step="1" required placeholder="Ej: 10">
            <div class="hint">Se registrará como ingreso (+).</div>
          </div>

          <div>
            <label for="price">Precio Unitario</label>
            <input type="number" id="price" name="price" min="0" step="0.01" placeholder="Ej: 15.50">
            <div class="hint">Costo por unidad (opcional, para finanzas).</div>
          </div>

          <div>
            <label for="supplier">Proveedor *</label>
            <input type="text" id="supplier" name="supplier" maxlength="120" required placeholder="Nombre del proveedor">
          </div>

          <div>
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Opcional: guía, factura, observaciones..."></textarea>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php?controller=<?php echo $useEmployeeHeader ? 'dashboard&action=logisticsPurchases' : 'admin&action=listPurchases'; ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary">Registrar compra</button>
      </div>
    </form>
</div>
