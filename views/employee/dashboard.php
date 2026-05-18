<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Empleado | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link
    rel="icon"
    href="<?php echo BASE_URL; ?>/assets/img/LogoPecosol.png"
    type="image/png"
  />

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
      max-width: 1000px;
      margin: 50px auto;
      padding: 0 20px;
    }

    h1 {
      text-align: center;
      color: #00fff0;
      margin-bottom: 40px;
      font-size: 2rem;
    }

    .card {
      background-color: #0f3460;
      border-radius: 16px;
      padding: 25px;
      text-align: center;
      box-shadow: 0 0 20px rgba(0,255,240,0.1);
      transition: transform 0.2s ease;
      margin-bottom: 40px;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h3 {
      color: #a0a0a0;
      font-size: 1rem;
      margin-bottom: 12px;
    }

    .card p {
      font-size: 28px;
      font-weight: bold;
      color: #00fff0;
      margin: 0;
    }

    h2 {
      font-size: 1.4rem;
      color: #00fff0;
      margin-bottom: 20px;
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

    p.no-data {
      color: #a0a0a0;
      font-style: italic;
      margin-top: 16px;
    }

    @media(max-width: 768px) {
      table {
        display: block;
        overflow-x: auto;
      }

      .card p {
        font-size: 24px;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/partials/header.php'; ?>

  <div class="container">
    <!-- Título principal -->
    <h1>Panel de Empleado</h1>

    <!-- Tarjeta con total de ventas del día -->
    <div class="card">
      <h3>Mis Ventas Hoy</h3>
      <p>S/. <?php echo number_format($ventasHoyEmpleado, 2, '.', ','); ?></p>
    </div>

    <!-- Últimas ventas -->
    <h2>Últimas Ventas Realizadas</h2>
    <?php if (!empty($ultimasVentasEmpleado)): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario (S/.)</th>
            <th>Total (S/.)</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasVentasEmpleado as $venta): ?>
            <tr>
              <td><?php echo $venta->id; ?></td>
              <td><?php echo htmlspecialchars($venta->product_name); ?></td>
              <td><?php echo $venta->quantity; ?></td>
              <td>S/. <?php echo number_format($venta->unit_price, 2, '.', ','); ?></td>
              <td>S/. <?php echo number_format($venta->total_price, 2, '.', ','); ?></td>
              <td><?php echo formatSaleDate($venta->sale_date); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="no-data">No has registrado ventas recientemente.</p>
    <?php endif; ?>
  </div>

  <!-- Chatbot Widget -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
  <script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
