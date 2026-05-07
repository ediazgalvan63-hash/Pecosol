<?php
// views/roles/management_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Ejecutivo | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" type="image/png" />
  <script src="<?php echo BASE_URL; ?>assets/js/chart.umd.js"></script>
  <style>
    body {
      background: #081225;
      color: #e8f4ff;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .header-bar {
      background: linear-gradient(135deg, #0b1e41 0%, #081225 100%);
      padding: 22px 32px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .header-bar h1 {
      margin: 0;
      font-size: 2rem;
      color: #7fffd4;
    }
    .header-bar p {
      margin: 8px 0 0;
      color: #9fc9ff;
      max-width: 820px;
      line-height: 1.6;
    }
    .page-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 28px 24px 40px;
    }
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .kpi-card {
      background: rgba(7, 16, 38, 0.96);
      border: 1px solid rgba(127,255,212,0.14);
      border-radius: 20px;
      padding: 22px;
      box-shadow: 0 20px 35px rgba(0,0,0,0.12);
    }
    .kpi-card h3 {
      margin: 0 0 10px;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #9cc9ff;
    }
    .kpi-card strong {
      display: block;
      font-size: 2.4rem;
      color: #ffffff;
      margin-bottom: 6px;
    }
    .kpi-card small {
      color: #9fc9ff;
      display: block;
      margin-top: 6px;
    }
    .grid-two {
      display: grid;
      grid-template-columns: 1.6fr 1fr;
      gap: 18px;
      margin-top: 24px;
    }
    .panel-card {
      background: rgba(7, 16, 38, 0.96);
      border: 1px solid rgba(127,255,212,0.12);
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 18px 34px rgba(0,0,0,0.16);
    }
    .panel-card header {
      padding: 18px 22px;
      background: rgba(127,255,212,0.06);
      font-weight: 700;
      color: #cfefff;
      letter-spacing: 0.02em;
    }
    .panel-card .panel-body {
      padding: 20px 24px;
    }
    .panel-card .panel-body ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }
    .panel-card .panel-body li {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      color: #c9e2ff;
    }
    .panel-card .panel-body li:last-child {
      border-bottom: none;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 64px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(127,255,212,0.12);
      color: #e9fffb;
      font-size: 0.88rem;
      font-weight: 700;
    }
    .table-wrapper {
      margin-top: 24px;
    }
    .section-title {
      margin: 0 0 18px;
      color: #b4d6ff;
      font-size: 1.05rem;
      font-weight: 700;
    }
    .data-table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(7, 16, 38, 0.96);
      border: 1px solid rgba(127,255,212,0.12);
      border-radius: 18px;
      overflow: hidden;
    }
    .data-table thead {
      background: rgba(127,255,212,0.08);
    }
    .data-table th,
    .data-table td {
      padding: 14px 16px;
      color: #dbeeff;
      text-align: left;
    }
    .data-table tbody tr:hover {
      background: rgba(127,255,212,0.06);
    }
    .data-table td:last-child {
      text-align: right;
    }
    .chart-card {
      min-height: 340px;
    }
    @media (max-width: 960px) {
      .grid-two {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../employee/partials/header.php'; ?>
  <section class="header-bar">
    <div class="page-inner">
      <h1>Panel Ejecutivo</h1>
      <p>Visión consolidada de desempeño, alertas clave y métricas estratégicas para la dirección.</p>
    </div>
  </section>

  <main class="page-inner">
    <div class="kpi-grid">
      <article class="kpi-card">
        <h3>Productos registrados</h3>
        <strong><?php echo number_format($totalProducts); ?></strong>
        <small>Catálogo total de productos en el sistema.</small>
      </article>
      <article class="kpi-card">
        <h3>Stock total</h3>
        <strong><?php echo number_format($totalStock); ?></strong>
        <small>Unidades disponibles actualmente.</small>
      </article>
      <article class="kpi-card">
        <h3>Artículos críticos</h3>
        <strong><?php echo number_format($lowStockCount); ?></strong>
        <small>Productos próximos a reabastecer.</small>
      </article>
      <article class="kpi-card">
        <h3>Ventas últimos 7 días</h3>
        <strong>S/. <?php echo number_format($totalSalesWeek, 2, '.', ','); ?></strong>
        <small>Promedio diario: S/. <?php echo number_format($averageDailySales, 2, '.', ','); ?></small>
      </article>
    </div>

    <div class="grid-two">
      <div class="panel-card chart-card">
        <header>Ventas semanales</header>
        <div class="panel-body">
          <canvas id="salesTrendChart" width="100%" height="300"></canvas>
        </div>
      </div>

      <div class="panel-card">
        <header>Alertas ejecutivas</header>
        <div class="panel-body">
          <ul>
            <li><span>Productos en riesgo</span><span class="badge"><?php echo number_format($lowStockCount); ?></span></li>
            <li><span>Ventas recientes</span><span class="badge"><?php echo number_format(count($recentSales)); ?></span></li>
            <li><span>Eventos de auditoría</span><span class="badge"><?php echo number_format(count($recentAudits)); ?></span></li>
          </ul>
          <div style="margin-top:18px;">
            <p style="margin:0 0 10px; color:#b4d6ff; font-weight:600;">Productos con stock crítico</p>
            <ul style="list-style:none; margin:0; padding:0; color:#dbeeff;">
              <?php if (empty($lowStockAlerts)): ?>
                <li>No hay alertas de stock por ahora.</li>
              <?php else: ?>
                <?php foreach ($lowStockAlerts as $item): ?>
                  <li style="padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.06);">
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

    <div class="table-wrapper">
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
                <tr><td colspan="6" style="padding: 20px; text-align: center; color:#9fc9ff;">No hay ventas recientes.</td></tr>
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
    </div>

    <div class="table-wrapper" style="margin-top: 24px;">
      <h2 class="section-title">Últimos eventos de auditoría</h2>
      <div class="panel-card">
        <div class="panel-body" style="padding: 0;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Entidad</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentAudits)): ?>
                <tr><td colspan="4" style="padding: 20px; text-align: center; color:#9fc9ff;">No hay eventos recientes.</td></tr>
              <?php else: ?>
                <?php foreach ($recentAudits as $log): ?>
                  <tr>
                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($log->created_at ?? $log->log_date ?? ''))); ?></td>
                    <td><?php echo htmlspecialchars($log->user_name ?? 'Sistema'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($log->action)); ?></td>
                    <td><?php echo htmlspecialchars($log->entity); ?></td>
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
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Ventas semanales',
          data: data,
          borderColor: '#7fffd4',
          backgroundColor: 'rgba(127,255,212,0.16)',
          fill: true,
          tension: 0.3,
          pointRadius: 4,
          pointBackgroundColor: '#7fffd4',
          pointBorderColor: '#ffffff',
          pointHoverRadius: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#b4d6ff' } },
          y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#b4d6ff', callback: value => 'S/.' + value } }
        },
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: 'rgba(7,16,38,0.96)', titleColor: '#ffffff', bodyColor: '#e8f4ff' }
        }
      }
    });
  </script>
</body>
</html>
