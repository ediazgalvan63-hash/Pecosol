<?php
// views/roles/finance_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Finanzas | Pecosol</title>
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
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .kpi-card {
      background: rgba(8, 16, 32, 0.9);
      border: 1px solid rgba(0, 255, 240, 0.16);
      border-radius: 20px;
      padding: 24px 22px;
      box-shadow: 0 20px 36px rgba(0, 0, 0, 0.15);
      min-height: 150px;
    }
    .kpi-card h3 {
      margin: 0;
      font-size: 0.9rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #00fff0;
    }
    .kpi-card strong {
      display: block;
      margin: 16px 0 10px;
      font-size: 2.4rem;
      color: #ffffff;
    }
    .kpi-card small {
      color: #8fb8c8;
      display: block;
      line-height: 1.5;
    }
    .grid-2 {
      display: grid;
      grid-template-columns: 1.4fr 1fr;
      gap: 18px;
      margin-top: 30px;
    }
    .panel {
      background: rgba(8, 16, 32, 0.92);
      border: 1px solid rgba(0, 255, 240, 0.16);
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 18px 34px rgba(0, 0, 0, 0.18);
    }
    .panel header {
      padding: 18px 22px;
      background: rgba(0, 255, 240, 0.08);
      color: #c8e6d0;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .panel-body {
      padding: 20px 24px;
    }
    .panel-body ul {
      margin: 0;
      padding: 0;
      list-style: none;
    }
    .panel-body li {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      color: #c8e6d0;
    }
    .panel-body li:last-child {
      border-bottom: none;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 72px;
      padding: 8px 12px;
      border-radius: 999px;
      font-size: 0.88rem;
      font-weight: 700;
      background: rgba(0, 255, 240, 0.14);
      color: #d4f5d8;
    }
    .section-title {
      margin: 0 0 18px;
      font-size: 1.05rem;
      color: #a8c7e0;
      font-weight: 700;
    }
    .table-wrap {
      margin-top: 24px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(6, 12, 24, 0.96);
      border-radius: 16px;
      overflow: hidden;
    }
    th, td {
      padding: 14px 16px;
      color: #d4e8f0;
      text-align: left;
    }
    thead {
      background: rgba(74, 222, 128, 0.06);
    }
    tbody tr:hover {
      background: rgba(255,255,255,0.04);
    }
    .chart-card {
      min-height: 360px;
    }
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 14px;
      margin-top: 18px;
    }
    .action-box {
      border-radius: 18px;
      padding: 18px;
      background: rgba(10, 24, 48, 0.9);
      border: 1px solid rgba(0, 255, 240, 0.12);
      color: #e0f2e8;
      text-decoration: none;
    }
    .action-box h4 {
      margin: 0 0 8px;
      color: #00fff0;
      font-size: 0.95rem;
      text-decoration: none;
    }
    .action-box p {
      margin: 0;
      color: #8fb8c8;
      line-height: 1.5;
    }
    @media (max-width: 960px) {
      .grid-2 {
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
        <h1>Panel Financiero</h1>
        <p>Control integral de cuentas por cobrar y pagar, flujo de caja y análisis financiero operativo.</p>
      </div>
      <div class="quick-actions">
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=financePurchases">
          <h4>Revisar CxP</h4>
          <p>Ver historial de compras y cuentas por pagar para análisis financiero.</p>
        </a>
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=financeSales">
          <h4>Revisar CxC</h4>
          <p>Ver, editar y eliminar ventas para monitorear cuentas por cobrar.</p>
        </a>
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=financeReports">
          <h4>Reportes Financieros</h4>
          <p>Genera informes y KPIs para toma de decisiones.</p>
        </a>
      </div>
    </section>

    <div class="kpi-grid">
      <article class="kpi-card">
        <h3>Cuentas por Pagar</h3>
        <strong>S/. <?php echo number_format($totalCxP, 2, '.', ','); ?></strong>
        <small>Compras pendientes de pago en la semana.</small>
      </article>
      <article class="kpi-card">
        <h3>Cuentas por Cobrar</h3>
        <strong>S/. <?php echo number_format($totalCxC, 2, '.', ','); ?></strong>
        <small>Ventas pendientes de cobro en la semana.</small>
      </article>
      <article class="kpi-card">
        <h3>Flujo de Caja</h3>
        <strong class="<?php echo $cashFlow >= 0 ? 'positive' : 'negative'; ?>">
          S/. <?php echo number_format($cashFlow, 2, '.', ','); ?>
        </strong>
        <small><?php echo $cashFlow >= 0 ? 'Positivo' : 'Negativo'; ?> esta semana.</small>
      </article>
      <article class="kpi-card">
        <h3>Órdenes Activas</h3>
        <strong><?php echo number_format($activeWorkOrders); ?></strong>
        <small>Trabajos en proceso actualmente.</small>
      </article>
    </div>

    <div class="grid-2">
      <section class="panel chart-card">
        <header>Cuentas por Cobrar Semanal</header>
        <div class="panel-body" style="height: 360px;">
          <canvas id="cxcTrendChart" width="100%" height="320"></canvas>
        </div>
      </section>

      <section class="panel">
        <header>Resumen Ejecutivo</header>
        <div class="panel-body">
          <ul>
            <li><span>Total Compras</span><span class="badge"><?php echo number_format($countPurchases); ?></span></li>
            <li><span>Total Ventas</span><span class="badge"><?php echo number_format($countSales); ?></span></li>
            <li><span>Ratio CxC/CxP</span><span class="badge"><?php echo $totalCxP > 0 ? number_format($totalCxC / $totalCxP, 2) : 'N/A'; ?></span></li>
          </ul>
          <div style="margin-top:20px;">
            <h4 style="margin:0 0 12px; color:#9fd4a8;">Estado Financiero</h4>
            <p style="margin:0; color:#c8e6d0; line-height:1.6;">
              <?php if ($cashFlow > 0): ?>
                Flujo de caja positivo. Las cuentas por cobrar superan las cuentas por pagar.
              <?php elseif ($cashFlow < 0): ?>
                Flujo de caja negativo. Revisar pagos pendientes y cobros.
              <?php else: ?>
                Flujo de caja equilibrado. Mantener control de CxP y CxC.
              <?php endif; ?>
            </p>
          </div>
        </div>
      </section>
    </div>

    <div class="table-wrap">
      <h2 class="section-title">Últimas Compras (CxP)</h2>
      <div class="panel">
        <div class="panel-body" style="padding: 0;">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Proveedor</th>
                <th>Cantidad</th>
                <th>Fecha y Hora</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentPurchases)): ?>
                <tr><td colspan="5" style="padding: 20px; text-align: center; color:#8fb8c8;">No hay compras recientes.</td></tr>
              <?php else: ?>
                <?php foreach ($recentPurchases as $purchase): ?>
                  <tr>
                    <td>#<?php echo htmlspecialchars($purchase->id); ?></td>
                    <td><?php echo htmlspecialchars($purchase->product_name); ?></td>
                    <td><?php echo htmlspecialchars($purchase->supplier); ?></td>
                    <td><?php echo number_format($purchase->quantity); ?></td>
                    <td><?php echo htmlspecialchars(function_exists('formatSaleDate') ? formatSaleDate($purchase->purchase_date, 'd/m/Y H:i') : date('d/m/Y H:i', strtotime($purchase->purchase_date))); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="table-wrap" style="margin-top: 24px;">
      <h2 class="section-title">Últimas Ventas (CxC)</h2>
      <div class="panel">
        <div class="panel-body" style="padding: 0;">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentSales)): ?>
                <tr><td colspan="5" style="padding: 20px; text-align: center; color:#8fb8c8;">No hay ventas recientes.</td></tr>
              <?php else: ?>
                <?php foreach ($recentSales as $sale): ?>
                  <tr>
                    <td>#<?php echo htmlspecialchars($sale->id); ?></td>
                    <td><?php echo htmlspecialchars($sale->product_name); ?></td>
                    <td><?php echo htmlspecialchars($sale->client_name); ?></td>
                    <td>S/. <?php echo number_format($sale->total_price, 2, '.', ','); ?></td>
                    <td><?php echo htmlspecialchars(function_exists('formatSaleDate') ? formatSaleDate($sale->sale_date, 'd/m/Y') : date('d/m/Y', strtotime($sale->sale_date))); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script>
    const cxcLabels = <?php echo json_encode($cxcTrendLabels); ?>;
    const cxcData = <?php echo json_encode($cxcTrendData); ?>;
    const cxcChart = document.getElementById('cxcTrendChart').getContext('2d');
    new Chart(cxcChart, {
      type: 'line',
      data: {
        labels: cxcLabels,
        datasets: [{
          label: 'CxC S/.',
          data: cxcData,
          borderColor: '#00fff0',
          backgroundColor: 'rgba(0, 255, 240, 0.18)',
          fill: true,
          tension: 0.32,
          pointRadius: 4,
          pointBackgroundColor: '#00fff0',
          pointBorderColor: '#ffffff',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#a8c7e0' } },
          y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#a8c7e0', callback: value => 'S/.' + value } }
        },
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: 'rgba(6, 12, 24, 0.96)', titleColor: '#ffffff', bodyColor: '#d4e8f0' }
        }
      }
    });
  </script>
</body>
</html>