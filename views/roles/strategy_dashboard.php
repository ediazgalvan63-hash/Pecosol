<?php
// views/roles/strategy_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Estratégico | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" type="image/png" />
  <script src="<?php echo BASE_URL; ?>assets/js/chart.umd.js"></script>
  <style>
    body {
      background: #0f172a;
      color: #e3eaf7;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      min-height: 100vh;
    }

    .strategy-header {
      padding: 24px 32px 16px;
      background: linear-gradient(135deg, #142850 0%, #0f172a 100%);
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .strategy-header h1 {
      margin: 0;
      font-size: 2rem;
      color: #00fff0;
    }

    .strategy-header p {
      margin: 12px 0 0;
      color: #99b6d7;
      max-width: 720px;
      line-height: 1.6;
    }

    .page-container {
      max-width: 1220px;
      margin: 0 auto;
      padding: 30px 24px 48px;
    }

    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }

    .kpi-card {
      background: rgba(15, 34, 85, 0.94);
      border: 1px solid rgba(0,255,240,0.15);
      border-radius: 20px;
      padding: 22px 24px;
      box-shadow: 0 14px 32px rgba(0,0,0,0.18);
    }

    .kpi-card h3 {
      margin: 0 0 12px;
      font-size: 0.95rem;
      color: #9ddcff;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }

    .kpi-card strong {
      font-size: 2.4rem;
      display: block;
      color: #ffffff;
      line-height: 1;
    }

    .cards-row {
      display: grid;
      grid-template-columns: 1.4fr 1fr;
      gap: 18px;
      margin-top: 24px;
    }

    .panel-card {
      background: rgba(11, 24, 57, 0.96);
      border: 1px solid rgba(0,255,240,0.12);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 45px rgba(0,0,0,0.22);
    }

    .panel-card header {
      padding: 18px 24px;
      background: rgba(0,255,240,0.08);
      color: #c5f7ff;
      font-weight: 700;
      letter-spacing: 0.01em;
    }

    .panel-card .panel-body {
      padding: 20px 24px 24px;
    }

    .panel-card .panel-body ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .panel-card .panel-body ul li {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 0;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      color: #dbe7ff;
    }

    .panel-card .panel-body ul li span:last-child {
      color: #ffffff;
      font-weight: 700;
    }

    .table-section {
      margin-top: 24px;
    }

    .section-title {
      margin: 0 0 14px;
      color: #b8d8ff;
      font-size: 1.05rem;
      font-weight: 600;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(15, 34, 85, 0.92);
      border: 1px solid rgba(0,255,240,0.1);
      border-radius: 18px;
      overflow: hidden;
    }

    .data-table th,
    .data-table td {
      padding: 14px 16px;
      color: #dee7ff;
      text-align: left;
    }

    .data-table thead {
      background: rgba(0,255,240,0.06);
    }

    .data-table tbody tr:hover {
      background: rgba(0,255,240,0.08);
    }

    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 68px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(0,255,240,0.14);
      color: #d6fbff;
      font-weight: 700;
      font-size: 0.87rem;
    }

    @media (max-width: 960px) {
      .cards-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../employee/partials/header.php'; ?>
  <section class="strategy-header">
    <div class="page-container">
      <h1>Panel Estratégico</h1>
      <p>Visión ejecutiva de indicadores clave, alertas de riesgo y tendencias de ventas para la toma de decisiones estratégicas.</p>
    </div>
  </section>

  <main class="page-container">
    <div class="kpi-grid">
      <article class="kpi-card">
        <h3>Total de productos</h3>
        <strong><?php echo number_format($totalProducts); ?></strong>
        <p>Catálogo de productos registrados.</p>
      </article>

      <article class="kpi-card">
        <h3>Stock total</h3>
        <strong><?php echo number_format($totalStock); ?></strong>
        <p>Unidades disponibles en inventario.</p>
      </article>

      <article class="kpi-card">
        <h3>Artículos críticos</h3>
        <strong><?php echo number_format($lowStockCount); ?></strong>
        <p>Productos con stock en o por debajo del mínimo.</p>
      </article>

      <article class="kpi-card">
        <h3>Ventas últimos 7 días</h3>
        <strong>S/. <?php echo number_format($totalSalesWeek, 2, '.', ','); ?></strong>
        <p>Ingresos generados durante la última semana.</p>
      </article>
    </div>

    <div class="cards-row">
      <div class="panel-card">
        <header>Ventas Semanales</header>
        <div class="panel-body">
          <canvas id="salesTrendChart" width="100%" height="320"></canvas>
        </div>
      </div>

      <div class="panel-card">
        <header>Alertas estratégicas</header>
        <div class="panel-body">
          <ul>
            <li>
              <span>Productos bajos</span>
              <span><?php echo number_format($lowStockCount); ?></span>
            </li>
            <li>
              <span>Compras recientes</span>
              <span><?php echo number_format(count($recentPurchases)); ?></span>
            </li>
            <li>
              <span>Eventos de auditoría</span>
              <span><?php echo number_format(count($recentAudits)); ?></span>
            </li>
          </ul>

          <div style="margin-top: 20px;">
            <p style="margin:0 0 10px; color:#bcd9ff;">Productos en alerta:</p>
            <ul style="list-style:none; padding:0; margin:0;">
              <?php if (count($lowStockAlerts) === 0): ?>
                <li style="color:#a9c7ff;">No hay artículos en riesgo en este momento.</li>
              <?php else: ?>
                <?php foreach ($lowStockAlerts as $item): ?>
                  <li style="padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <strong><?php echo htmlspecialchars($item->name); ?></strong>
                    <span class="badge">Stock <?php echo number_format($item->stock); ?></span>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <section class="table-section">
      <h2 class="section-title">Últimas ventas</h2>
      <div class="panel-card">
        <div class="panel-body" style="padding: 0;">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Responsable</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentSales)): ?>
                <tr><td colspan="6" style="padding:20px; text-align:center; color:#9bb8db;">No hay ventas recientes.</td></tr>
              <?php else: ?>
                <?php foreach ($recentSales as $sale): ?>
                  <tr>
                    <td>#<?php echo htmlspecialchars($sale->id); ?></td>
                    <td><?php echo htmlspecialchars($sale->product_name); ?></td>
                    <td><?php echo htmlspecialchars($sale->user_name); ?></td>
                    <td><?php echo htmlspecialchars($sale->client_name); ?></td>
                    <td>S/. <?php echo number_format($sale->total_price, 2, '.', ','); ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($sale->sale_date))); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <script>
    const labels = <?php echo json_encode($salesTrendLabels); ?>;
    const data = <?php echo json_encode($salesTrendData); ?>;
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Ventas (S/.)',
          data: data,
          borderColor: '#00fff0',
          backgroundColor: 'rgba(0, 255, 240, 0.18)',
          fill: true,
          tension: 0.28,
          pointRadius: 4,
          pointBackgroundColor: '#00fff0',
          pointBorderColor: '#ffffff',
          pointHoverRadius: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#bcd9ff' } },
          y: {
            grid: { color: 'rgba(255,255,255,0.06)' },
            ticks: { color: '#bcd9ff', callback: value => 'S/.' + value }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: 'rgba(15, 34, 85, 0.96)', titleColor: '#ffffff', bodyColor: '#eaeaea' }
        }
      }
    });
  </script>
</body>
</html>
