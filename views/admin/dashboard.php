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

  <!-- Estilos generales -->
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

/* Container general */
.container {
  max-width: 1100px;
  margin: 50px auto;
  padding: 0 20px;
}

/* Título */
h1 {
  text-align: center;
  color: #00fff0;
  margin-bottom: 40px;
}

/* Tarjetas de resumen */
    .section-title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
      gap: 12px;
    }
    .section-title h2 {
      margin: 0;
      color: #00fff0;
      font-size: 1.4rem;
    }
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .card {
      background-color: #0f3460;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 0 20px rgba(0,255,240,0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: 1px solid rgba(0,255,240,0.16);
    }
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 28px rgba(0,255,240,0.18);
    }
    .card h3 {
      color: #a0a0a0;
      font-size: 0.95rem;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .card p {
      font-size: 32px;
      font-weight: 700;
      color: #00fff0;
      margin: 0;
    }
    .card small {
      display: block;
      color: #9ae7ff;
      margin-top: 10px;
    }

    .kpi-group {
      margin-bottom: 40px;
    }

  background-color: #16213e;
  border-radius: 16px;
  padding: 25px;
  margin-bottom: 40px;
  box-shadow: 0 0 15px rgba(0,255,240,0.08);
}
.chart-container h2 {
  color: #00fff0;
  margin-bottom: 20px;
  font-size: 20px;
}

/* Tabla de ventas */
.lst-ventas {
  color: #00fff0;
  margin-bottom: 15px;
  font-size: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  background-color: #16213e;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 0 12px rgba(0,255,240,0.1);
}

th, td {
  padding: 14px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  text-align: left;
}

th {
  background-color: #0f3460;
  color: #00fff0;
  font-weight: 600;
}

tr:last-child td {
  border-bottom: none;
}

/* ─── NAVBAR MODERNO ───────────────────────────── */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #0f172a; 
  padding: 12px 30px;
  border-bottom: 2px solid #00fff0;
  box-shadow: 0 2px 10px rgba(0, 255, 240, 0.05);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.navbar-left img {
  height: 28px;
}

.navbar-left span {
  font-size: 18px;
  font-weight: bold;
  color: #00fff0;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 25px;
}

.navbar-right a {
  text-decoration: none;
  color: #e0e0e0;
  font-weight: 500;
  transition: color 0.2s;
}

.navbar-right a:hover {
  color: #00fff0;
}

.navbar-right .logout {
  color: #ff6b6b;
  font-weight: bold;
}


  </style>
</head>
<body>

  <!-- Header -->
  <?php include __DIR__ . '/partials/header.php'; ?>

  <div class="container">
    <!-- Título principal -->
    <h1>Panel de Administración</h1>

    <div class="kpi-group">
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
          <p style="color:#ff8b8b;"><?php echo (int)$productosBajoStock; ?></p>
          <small>Alertas de reabastecimiento activas</small>
        </div>
        <div class="card">
          <h3>Movimientos hoy</h3>
          <p><?php echo (int)$movimientosHoy; ?></p>
          <small>Entradas y salidas registradas hoy</small>
        </div>
      </div>
    </div>

    <div class="kpi-group">
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
    <div class="chart-container">
      <h2>Ventas Últimos 7 Días</h2>
      <canvas id="ventasChart" width="800" height="300"></canvas>
    </div>

    <!-- Últimas Ventas -->
    <h2 class="lst-ventas">Últimas Ventas</h2>
    <?php if (!empty($ultimasVentas)): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Empleado</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Total</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasVentas as $venta): ?>
            <tr>
              <td><?php echo $venta->id; ?></td>
              <td><?php echo htmlspecialchars($venta->user_name); ?></td>
              <td><?php echo htmlspecialchars($venta->product_name); ?></td>
              <td><?php echo $venta->quantity; ?></td>
              <td>S/. <?php echo number_format($venta->unit_price, 2, '.', ','); ?></td>
              <td>S/. <?php echo number_format($venta->total_price, 2, '.', ','); ?></td>
              <td><?php echo date('d-m-Y H:i', strtotime($venta->sale_date)); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="color:#a0a0a0;">No hay ventas registradas aún.</p>
    <?php endif; ?>

    <h2 class="lst-ventas" style="margin-top:28px;">Ultimos Movimientos de Inventario</h2>
    <?php if (!empty($ultimosMovimientos)): ?>
      <table>
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
              <td><?php echo date('d-m-Y H:i', strtotime($mov->movement_date)); ?></td>
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
      <p style="color:#a0a0a0;">No hay movimientos registrados aún.</p>
    <?php endif; ?>
  </div>

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

  <!-- Chatbot Widget -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
  <script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
