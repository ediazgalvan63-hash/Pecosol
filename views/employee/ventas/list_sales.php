<?php
// views/employee/ventas/list_sales.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Mis Ventas | Pecosol</title>
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
      margin-bottom: 30px;
      color: #00fff0;
      font-size: 2rem;
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

    td {
      color: #eaeaea;
    }

    tr:last-child td {
      border-bottom: none;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      font-style: italic;
      color: #a0a0a0;
      background-color: #0f172a;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,255,240,0.05);
      margin-top: 30px;
    }

    @media(max-width: 768px) {
      table {
        display: block;
        overflow-x: auto;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/../partials/header.php'; ?>

  <div class="container">
    <h1>Mis Ventas</h1>

    <?php if (!empty($ventas)): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Total</th>
            <th>Cliente</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ventas as $venta): ?>
            <tr>
              <td><?php echo $venta->id; ?></td>
              <td><?php echo htmlspecialchars($venta->product_name); ?></td>
              <td><?php echo $venta->quantity; ?></td>
              <td>S/. <?php echo number_format($venta->unit_price, 2, '.', ','); ?></td>
              <td>S/. <?php echo number_format($venta->total_price, 2, '.', ','); ?></td>
              <td><?php echo htmlspecialchars($venta->client_name ?? 'Cliente General'); ?></td>
              <td><?php echo formatSaleDate($venta->sale_date); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="no-data">
        <p>No has registrado ninguna venta aún.</p>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
