<?php
// views/roles/commercial_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Comercial | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" type="image/png" />
  <script src="<?php echo BASE_URL; ?>assets/js/chart.umd.js"></script>
  <style>
    body {
      margin: 0;
      background: linear-gradient(180deg, #08101f 0%, #14192d 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #f1f8ff;
    }
    .page-shell {
      max-width: 1180px;
      margin: 0 auto;
      padding: 24px 20px 40px;
    }
    .hero {
      display: grid;
      gap: 18px;
      margin-bottom: 30px;
    }
    .hero h1 {
      margin: 0;
      font-size: 2.4rem;
      color: #7de7ff;
    }
    .hero p {
      margin: 8px 0 0;
      color: #c7dbff;
      max-width: 860px;
      line-height: 1.6;
    }
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .kpi-card {
      background: rgba(11, 25, 49, 0.9);
      border: 1px solid rgba(125, 231, 255, 0.16);
      border-radius: 20px;
      padding: 22px 20px;
      box-shadow: 0 18px 32px rgba(0, 0, 0, 0.15);
      min-height: 140px;
    }
    .kpi-card h3 {
      margin: 0;
      font-size: 0.9rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #8cc7ff;
    }
    .kpi-card strong {
      display: block;
      margin: 14px 0 8px;
      font-size: 2.2rem;
      color: #ffffff;
    }
    .kpi-card small {
      color: #9dbce3;
      display: block;
      line-height: 1.5;
    }
    .grid-2 {
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 18px;
      margin-top: 28px;
    }
    .panel {
      background: rgba(11, 25, 49, 0.92);
      border: 1px solid rgba(125, 231, 255, 0.14);
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 20px 38px rgba(0, 0, 0, 0.16);
    }
    .panel header {
      padding: 18px 22px;
      background: rgba(125, 231, 255, 0.08);
      color: #d7ecff;
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
      color: #d5e7ff;
    }
    .panel-body li:last-child {
      border-bottom: none;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 70px;
      padding: 8px 12px;
      border-radius: 999px;
      font-size: 0.88rem;
      font-weight: 700;
      background: rgba(125, 231, 255, 0.14);
      color: #edf9ff;
    }
    .section-title {
      margin: 0 0 18px;
      font-size: 1.05rem;
      color: #b7d6ff;
      font-weight: 700;
    }
    .table-wrap {
      margin-top: 24px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(10, 20, 38, 0.96);
      border-radius: 14px;
      overflow: hidden;
    }
    th, td {
      padding: 14px 16px;
      color: #d9e9ff;
      text-align: left;
    }
    thead {
      background: rgba(125, 231, 255, 0.06);
    }
    tbody tr:hover {
      background: rgba(255,255,255,0.04);
    }
    .chart-card {
      min-height: 360px;
    }
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 14px;
      margin-top: 16px;
    }
    .action-box {
      border-radius: 18px;
      padding: 16px;
      background: rgba(14, 36, 70, 0.9);
      border: 1px solid rgba(125, 231, 255, 0.12);
      color: #e8f5ff;
    }
    .action-box h4 {
      margin: 0 0 8px;
      color: #b5ecff;
      font-size: 0.95rem;
    }
    .action-box p {
      margin: 0;
      color: #aec8e6;
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
        <h1>Panel Comercial</h1>
        <p>Acceso inmediato a la gestión de ventas, cartera de clientes y rendimiento personal.</p>
      </div>
      <div class="quick-actions">
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=employee&action=addSaleForm">
          <h4>Registrar nueva venta</h4>
          <p>Agrega ventas con rapidez y conserva el seguimiento comercial al instante.</p>
        </a>
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=employee&action=listSalesEmployee">
          <h4>Mis ventas</h4>
          <p>Revisa tu historial comercial y enfócate en tu pipeline más reciente.</p>
        </a>
        <a class="action-box" href="<?php echo BASE_URL; ?>index.php?controller=employee&action=listProductsEmployee">
          <h4>Catálogo</h4>
          <p>Consulta productos disponibles y selecciona ofertas que cierren rápido.</p>
        </a>
      </div>
    </section>

    <div class="kpi-grid">
      <article class="kpi-card">
        <h3>Ventas hoy</h3>
        <strong>S/. <?php echo number_format($totalSalesToday, 2, '.', ','); ?></strong>
        <small>Ingreso registrado en tu gestión comercial este día.</small>
      </article>
      <article class="kpi-card">
        <h3>Facturación semanal</h3>
        <strong>S/. <?php echo number_format($totalSalesWeek, 2, '.', ','); ?></strong>
        <small>Ventas acumuladas de los últimos 7 días.</small>
      </article>
      <article class="kpi-card">
        <h3>Ventas en el día</h3>
        <strong><?php echo number_format($salesCountToday); ?></strong>
        <small>Cantidad de transacciones cerradas hoy.</small>
      </article>
      <article class="kpi-card">
        <h3>Ticket promedio</h3>
        <strong>S/. <?php echo number_format($averageSaleValue, 2, '.', ','); ?></strong>
        <small>Valor promedio de tus ventas del día.</small>
      </article>
    </div>

    <div class="grid-2">
      <section class="panel chart-card">
        <header>Rendimiento diario</header>
        <div class="panel-body" style="height: 360px;">
          <canvas id="salesTrendChart" width="100%" height="320"></canvas>
        </div>
      </section>

      <section class="panel">
        <header>Clientes principales</header>
        <div class="panel-body">
          <?php if (!empty($topClients)): ?>
            <ul>
              <?php foreach ($topClients as $client): ?>
                <li>
                  <span><?php echo htmlspecialchars($client->client_name); ?></span>
                  <span class="badge">S/. <?php echo number_format($client->total_revenue, 2, '.', ','); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>No hay clientes destacados en este período.</p>
          <?php endif; ?>
          <div style="margin-top: 18px;">
            <h4 style="margin:0 0 10px; color:#b5e0ff;">Productos más vendidos</h4>
            <ul>
              <?php if (!empty($topProducts)): ?>
                <?php foreach ($topProducts as $product): ?>
                  <li>
                    <span><?php echo htmlspecialchars($product->product_name); ?></span>
                    <span class="badge">S/. <?php echo number_format($product->total_revenue, 2, '.', ','); ?></span>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li>No hay productos vendidos aún.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </section>
    </div>

    <div class="table-wrap">
      <h2 class="section-title">Últimas ventas personales</h2>
      <div class="panel">
        <div class="panel-body" style="padding: 0;">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th>Total</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentSales)): ?>
                <tr><td colspan="5" style="padding: 20px; text-align: center; color:#a0c4ff;">No has registrado ventas recientemente.</td></tr>
              <?php else: ?>
                <?php foreach ($recentSales as $sale): ?>
                  <tr>
                    <td>#<?php echo htmlspecialchars($sale->id); ?></td>
                    <td><?php echo htmlspecialchars($sale->client_name); ?></td>
                    <td><?php echo htmlspecialchars($sale->product_name); ?></td>
                    <td>S/. <?php echo number_format($sale->total_price, 2, '.', ','); ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($sale->sale_date))); ?></td>
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
    const labels = <?php echo json_encode($salesTrendLabels); ?>;
    const data = <?php echo json_encode($salesTrendData); ?>;
    const salesChart = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesChart, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Ventas S/.',
          data: data,
          borderColor: '#7de7ff',
          backgroundColor: 'rgba(125, 231, 255, 0.18)',
          fill: true,
          tension: 0.32,
          pointRadius: 4,
          pointBackgroundColor: '#7de7ff',
          pointBorderColor: '#ffffff',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#cfefff' } },
          y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#cfefff', callback: value => 'S/.' + value } }
        },
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: 'rgba(12, 20, 42, 0.96)', titleColor: '#ffffff', bodyColor: '#e5f3ff' }
        }
      }
    });
  </script>
</body>
</html>
