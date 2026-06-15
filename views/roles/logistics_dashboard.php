<?php
// views/roles/logistics_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Logística | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" type="image/png" />
  <script src="<?php echo BASE_URL; ?>assets/js/chart.umd.js"></script>
  <style>
    body {
      margin: 0;
      background: linear-gradient(180deg, #0a0f1a 0%, #1a1d2a 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #e8f4ff;
    }
    .page-shell {
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px 20px 40px;
    }
    .hero {
      display: grid;
      gap: 16px;
      margin-bottom: 32px;
    }
    .hero h1 {
      margin: 0;
      font-size: 2.5rem;
      color: #00fff0;
    }
    .hero p {
      margin: 8px 0 0;
      color: #a8c7e0;
      max-width: 880px;
      line-height: 1.6;
    }
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 16px;
    }
    .action-box {
      display: block;
      background: rgba(0, 255, 240, 0.08);
      border: 1px solid rgba(0, 255, 240, 0.18);
      border-radius: 12px;
      padding: 20px;
      text-decoration: none;
      color: #e8f4ff;
      transition: all 0.3s ease;
    }
    .action-box:hover {
      background: rgba(0, 255, 240, 0.12);
      border-color: rgba(0, 255, 240, 0.3);
      transform: translateY(-2px);
    }
    .action-box h4 {
      margin: 0 0 8px;
      color: #00fff0;
      font-size: 1.2rem;
    }
    .action-box p {
      margin: 0;
      color: #a8c7e0;
      font-size: 0.9rem;
      line-height: 1.5;
    }
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .kpi-card {
      background: rgba(22, 33, 62, 0.8);
      border: 1px solid rgba(0, 255, 240, 0.16);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
    }
    .kpi-card h3 {
      margin: 0 0 12px;
      color: #00fff0;
      font-size: 1rem;
      font-weight: 600;
    }
    .kpi-card strong {
      display: block;
      font-size: 2rem;
      color: #e8f4ff;
      margin-bottom: 8px;
    }
    .kpi-card small {
      color: #a8c7e0;
      font-size: 0.85rem;
    }
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-top: 32px;
    }
    .panel {
      background: rgba(22, 33, 62, 0.8);
      border: 1px solid rgba(0, 255, 240, 0.16);
      border-radius: 12px;
      overflow: hidden;
    }
    .panel header {
      background: rgba(0, 255, 240, 0.08);
      color: #00fff0;
      padding: 16px 20px;
      font-weight: 600;
      border-bottom: 1px solid rgba(0, 255, 240, 0.16);
    }
    .panel-body {
      padding: 20px;
    }
    .table-wrap {
      margin-top: 32px;
    }
    .section-title {
      margin: 0 0 16px;
      color: #00fff0;
      font-size: 1.5rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(15, 23, 42, 0.5);
      border-radius: 8px;
      overflow: hidden;
    }
    thead th {
      background: rgba(0, 255, 240, 0.08);
      color: #a8c7e0;
      padding: 12px 16px;
      text-align: left;
      font-weight: 600;
      border-bottom: 1px solid rgba(0, 255, 240, 0.16);
    }
    tbody td {
      padding: 12px 16px;
      border-bottom: 1px solid rgba(0, 255, 240, 0.08);
      color: #e8f4ff;
    }
    tbody tr:hover {
      background: rgba(0, 255, 240, 0.05);
    }
    .badge {
      background: #00fff0;
      color: #0a0f1a;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    @media (max-width: 768px) {
      .grid-2 {
        grid-template-columns: 1fr;
      }
      .quick-actions {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../employee/partials/header.php'; ?>
  <main class="page-shell">
    <section class="hero">
      <div>
        <h1>Panel Logística</h1>
        <p>Gestión integral de inventario, abastecimiento y órdenes de trabajo. Controla el flujo de productos y optimiza las operaciones de almacén.</p>
      </div>
      <div class="quick-actions">
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=logisticsPurchases">
          <h4>Gestionar Compras</h4>
          <p>Registrar nuevas compras, editar y eliminar entradas de abastecimiento.</p>
        </a>
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=logisticsInventory">
          <h4>Control de Inventario</h4>
          <p>Monitorear movimientos de stock y kardex de productos.</p>
        </a>
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=logisticsWorkOrders">
          <h4>Órdenes de Trabajo</h4>
          <p>Crear y gestionar órdenes de trabajo para operaciones especiales.</p>
        </a>
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=logisticsRecount">
          <h4>Reconteo de Inventario</h4>
          <p>Ajustes por inventario físico y correcciones de stock.</p>
        </a>
      </div>
    </section>

    <div class="kpi-grid">
      <article class="kpi-card">
        <h3>Productos Totales</h3>
        <strong><?php echo number_format($totalProducts); ?></strong>
        <small>Productos registrados en el sistema.</small>
      </article>
      <article class="kpi-card">
        <h3>Movimientos</h3>
        <strong><?php echo number_format($totalMovements); ?></strong>
        <small>Movimientos de inventario registrados.</small>
      </article>
      <article class="kpi-card">
        <h3>Órdenes Activas</h3>
        <strong><?php echo number_format($activeWorkOrders); ?></strong>
        <small>Trabajos en proceso actualmente.</small>
      </article>
      <article class="kpi-card">
        <h3>Compras Recientes</h3>
        <strong><?php echo count($recentPurchases); ?></strong>
        <small>Compras en las últimas semanas.</small>
      </article>
    </div>

    <div class="table-wrap">
      <h2 class="section-title">Últimas Compras de Abastecimiento</h2>
      <div class="panel">
        <div class="panel-body" style="padding: 0;">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Proveedor</th>
                <th>Cantidad</th>
                <th>Precio Unitario (S/.)</th>
                <th>Fecha y Hora</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentPurchases)): ?>
                <tr><td colspan="6" style="padding: 20px; text-align: center; color:#8fb8c8;">No hay compras recientes.</td></tr>
              <?php else: ?>
                <?php foreach ($recentPurchases as $purchase): ?>
                  <tr>
                    <td>#<?php echo htmlspecialchars($purchase->id); ?></td>
                    <td><?php echo htmlspecialchars($purchase->product_name); ?></td>
                    <td><?php echo htmlspecialchars($purchase->supplier); ?></td>
                    <td><?php echo number_format($purchase->quantity); ?></td>
                    <td>S/. <?php echo number_format($purchase->price, 2, '.', ','); ?></td>
                    <td><?php echo htmlspecialchars(function_exists('formatSaleDate') ? formatSaleDate($purchase->purchase_date, 'd/m/Y H:i') : date('d/m/Y H:i', strtotime($purchase->purchase_date))); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</body>
</html>