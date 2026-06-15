<?php
// views/admin/dashboard.php

/**
 * Variables que envía el controlador DashboardController:
 *   $ventasHoy     (float): total de ventas del día
 *   $ventasMes     (float): total de ventas del mes
 *   $totalStock    (int)  : total de productos en stock
 *   $ultimasVentas (array): arreglo de objetos con las últimas ventas
 *   $datosSemana   (array): arreglo de 7 elementos con:
 *       [
 *         'etiqueta' => 'DD/MM',
 *         'valor'    => total de ventas ese día (float)
 *       ]
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Control Admin | Pecosol</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Favicon -->
  <link rel="icon"
        href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png"
        type="image/png">

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <!-- Custom app styles -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css" />


</head>
<body class="admin-panel">

  <!-- Header -->
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="page-shell">
    <section class="header-panel">
      <h1>Panel de Administración</h1>
      <p>Monitorea la operación comercial y de inventario con una experiencia uniforme y profesional.</p>
    </section>

    <div class="kpi-group module">
      <div class="section-title">
        <h2>Inventario</h2>
      </div>
      <div class="cards">
        <div class="card">
          <h3>Total productos</h3>
          <p><?php echo (int)$totalProductos; ?></p>
          <small>Registro completo de SKUs</small>
        </div>
        <div class="card">
          <h3>Stock total</h3>
          <p><?php echo $totalStock; ?> unidades</p>
          <small>Volumen disponible en inventario</small>
        </div>
        <div class="card">
          <h3>Productos con bajo stock</h3>
          <p class="warning-text"><?php echo (int)$productosBajoStock; ?></p>
          <small>Alertas de reabastecimiento activas</small>
        </div>
        <div class="card">
          <h3>Movimientos hoy</h3>
          <p><?php echo (int)$movimientosHoy; ?></p>
          <small>Entradas y salidas registradas hoy</small>
        </div>
      </div>
    </div>

    <div class="kpi-group module">
      <div class="section-title">
        <h2>Ventas</h2>
      </div>
      <div class="cards">
        <div class="card">
          <h3>Ventas hoy</h3>
          <p>S/. <?php echo number_format($ventasHoy, 2, '.', ','); ?></p>
          <small>Facturación del día</small>
        </div>
        <div class="card">
          <h3>Ventas mes</h3>
          <p>S/. <?php echo number_format($ventasMes, 2, '.', ','); ?></p>
          <small>Performance mensual</small>
        </div>
        <div class="card">
          <h3>Entradas / Salidas</h3>
          <p><?php echo (int)$totalEntradas; ?> / <?php echo (int)$totalSalidas; ?></p>
          <small>Balance del flujo de inventario</small>
        </div>
      </div>
    </div>

    <!-- Gráfica de barras -->
    <div class="chart-container module">
      <h2>Ventas Últimos 7 Días</h2>
      <canvas id="ventasChart" width="800" height="300"></canvas>
    </div>
    <!-- Últimas Ventas -->
    <section class="module">
      <h2 class="section-title">Últimas Ventas</h2>
      <?php if (!empty($ultimasVentas)): ?>
        <table class="dashboard-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Empleado</th>
            <th>Producto</th>
            <th>Cliente</th>
            <th>Cantidad</th>
            <th>Precio Unitario (S/.)</th>
            <th>Total (S/.)</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasVentas as $venta): ?>
            <tr>
              <td><?php echo $venta->id; ?></td>
              <td><?php echo htmlspecialchars($venta->user_name); ?></td>
              <td><?php echo htmlspecialchars($venta->product_name); ?></td>
              <td><?php echo htmlspecialchars($venta->client_name ?? 'Cliente General'); ?></td>
              <td><?php echo $venta->quantity; ?></td>
              <td>S/. <?php echo number_format($venta->unit_price, 2, '.', ','); ?></td>
              <td>S/. <?php echo number_format($venta->total_price, 2, '.', ','); ?></td>
              <td><?php echo formatSaleDate($venta->sale_date); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        </table>
      <?php else: ?>
        <p class="empty-state">No hay ventas registradas aún.</p>
      <?php endif; ?>
    </section>

    <section class="module">
      <h2 class="section-title">Ultimos Movimientos de Inventario</h2>
      <?php if (!empty($ultimosMovimientos)): ?>
        <table class="dashboard-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Usuario</th>
            <th>Motivo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimosMovimientos as $mov): ?>
            <tr>
              <td><?php echo function_exists('formatSaleDate') ? formatSaleDate($mov->movement_date, 'd-m-Y H:i') : date('d-m-Y H:i', strtotime($mov->movement_date)); ?></td>
              <td><?php echo strtoupper($mov->movement_type); ?></td>
              <td><?php echo htmlspecialchars($mov->product_name); ?></td>
              <td><?php echo abs((int)$mov->quantity_change); ?></td>
              <td><?php echo htmlspecialchars($mov->user_name); ?></td>
              <td><?php echo htmlspecialchars($mov->notes ?? ''); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        </table>
      <?php else: ?>
        <p class="empty-state">No hay movimientos registrados aún.</p>
      <?php endif; ?>
    </section>
    
    <!-- Bitácora de auditoría removida del dashboard de administrador por solicitud -->
  </main>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo BASE_URL; ?>assets/js/chart.umd.js"></script>
  <script>
    const etiquetas = <?php echo json_encode(array_column($datosSemana, 'etiqueta')); ?>;
    const datos     = <?php echo json_encode(array_column($datosSemana, 'valor')); ?>;
    const ctx = document.getElementById('ventasChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: etiquetas,
        datasets: [{
          label: 'Ventas (S/.)',
          data: datos,
          backgroundColor: 'rgba(0, 255, 240, 0.5)',
          borderColor:   'rgba(0, 255, 240, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: { beginAtZero: true }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  </script>
</body>
</html>
