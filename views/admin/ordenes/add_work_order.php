<?php
$role = $_SESSION['role'] ?? '';
$useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia', 'supervisor'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nueva orden de trabajo</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
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
        max-width: 980px;
        margin: 38px auto;
        padding: 0 16px;
      }

      .page-head {
        display:flex;
        justify-content: space-between;
        align-items:flex-start;
        gap: 16px;
        margin-bottom: 18px;
        flex-wrap: wrap;
      }

      .page-head h1 {
        margin: 0;
        color: #00fff0;
        font-size: 2rem;
        line-height: 1.15;
      }

      .page-head p {
        margin: 8px 0 0;
        color: #a0cfe8;
        max-width: 760px;
      }

      .error {
        margin: 14px 0;
        margin: 14px 0;
        background: rgba(255, 75, 75, 0.14);
        border: 1px solid rgba(255, 107, 107, 0.55);
        color: #ffb1b1;
        padding: 10px 12px;
        border-radius: 12px;
      }

      .form-shell {
        background: rgba(22, 33, 62, 0.94);
        border: 1px solid rgba(0,255,240,0.18);
        border-radius: 16px;
        box-shadow: 0 12px 34px rgba(0,0,0,0.35);
        overflow: hidden;
      }

      .form-body { padding: 18px; }

      .grid {
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
      }

      @media (max-width: 900px) {
        .grid { grid-template-columns: 1fr; }
      }

      .span-2 { grid-column: span 2; }
      @media (max-width: 900px) { .span-2 { grid-column: span 1; } }

      label {
        display:block;
        margin: 0 0 6px;
        color:#cfefff;
        font-weight: 600;
        letter-spacing: 0.01em;
      }

      select, input, textarea {
        width:100%;
        background:#0f172a;
        border: 1px solid rgba(0,255,240,0.22);
        color:#eaeaea;
        border-radius: 12px;
        padding: 12px 12px;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
        box-sizing: border-box;
      }

      textarea { resize: vertical; min-height: 92px; }

      select:focus, input:focus, textarea:focus {
        border-color: #00fff0;
        box-shadow: 0 0 0 4px rgba(0,255,240,0.10);
      }

      .hint { margin-top: 8px; color:#9ae7ff; font-size: 0.92rem; }

      .form-actions {
        display:flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 18px;
        border-top: 1px solid rgba(255,255,255,0.08);
        background: rgba(15, 23, 42, 0.55);
      }

      .btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:10px;
        padding: 11px 16px;
        border-radius: 999px;
        border: 1px solid rgba(0,255,240,0.25);
        text-decoration: none;
        cursor:pointer;
        font-weight: 800;
        letter-spacing: 0.02em;
        transition: transform .12s ease, background-color .12s ease;
      }
    </style>
</head>
<body>
<?php
if ($useEmployeeHeader) {
    include __DIR__ . '/../../employee/partials/header.php';
} else {
    include __DIR__ . '/../partials/header.php';
}
?>
<div class="container">
    <div class="page-head">
      <div>
        <h1>Nueva orden de trabajo</h1>
        <p>Registra el servicio y deja trazabilidad. Opcionalmente puedes vincular la orden a una venta existente.</p>
      </div>
      <div>
        <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php?controller=<?php echo $useEmployeeHeader ? 'dashboard&action=logisticsWorkOrders' : 'admin&action=listWorkOrders'; ?>">← Volver</a>
      </div>
    </div>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=storeWorkOrder" class="form-shell">
      <div class="form-body">
        <div class="grid">
          <div>
            <label for="client_name">Cliente *</label>
            <input id="client_name" type="text" name="client_name" required placeholder="Nombre del cliente">
          </div>

          <div>
            <label for="technician_name">Técnico *</label>
            <input id="technician_name" type="text" name="technician_name" required placeholder="Nombre del técnico">
          </div>

          <div class="span-2">
            <label for="service_type">Tipo de servicio *</label>
            <input id="service_type" type="text" name="service_type" required placeholder="Ej: Instalación, mantenimiento, revisión...">
          </div>

          <div>
            <label for="status">Estado *</label>
            <select id="status" name="status" required>
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En proceso</option>
                <option value="finalizado">Finalizado</option>
            </select>
            <div class="hint">Puedes actualizar el estado luego desde el listado.</div>
          </div>

          <div>
            <label for="sale_id">Vincular a venta (opcional)</label>
            <select id="sale_id" name="sale_id">
                <option value="">Sin vincular</option>
                <?php foreach ($ventas as $v): ?>
                    <option value="<?php echo (int)$v->id; ?>">
                        Venta #<?php echo (int)$v->id; ?> — <?php echo htmlspecialchars($v->product_name); ?> — S/. <?php echo number_format((float)$v->total_price, 2, '.', ','); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="hint">Si vinculas una venta, tendrás trazabilidad completa.</div>
          </div>

          <div class="span-2">
            <label for="materials_used">Materiales utilizados</label>
            <textarea id="materials_used" name="materials_used" rows="3" placeholder="Opcional: lista de materiales, repuestos, etc."></textarea>
          </div>

          <div class="span-2">
            <label for="notes">Observaciones</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Opcional: detalles del servicio, condiciones, observaciones..."></textarea>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>index.php?controller=<?php echo $useEmployeeHeader ? 'dashboard&action=logisticsWorkOrders' : 'admin&action=listWorkOrders'; ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary">Registrar orden</button>
      </div>
    </form>
</div>
</body>
</html>
