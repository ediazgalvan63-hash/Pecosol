<?php
// views/admin/ventas/list_sales.php
// Variables disponibles:
//   $ventas  (array de objetos con id, user_id, user_name, product_id, product_name,
//             quantity, unit_price, total_price, description, sale_date)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Listado de Ventas | Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">

    <link
    rel="icon"
    href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png"
    type="image/png"
  />
    <style>
    /* ─── 1) Fondo y tipografía ───────────────────────────────── */
    body {
  background-color: #1a1a2e;
  background-image: url('<?php echo BASE_URL; ?>assets/img/overlapping-circles.svg');
  background-repeat: repeat;
  background-size: 60px;
  background-attachment: fixed;
}

    .container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
      color: #00fff0;
    }

    /* ─── 2) Buscador ───────────────────────────────────────── */
    .search-box {
      margin-bottom: 20px;
      text-align: right;
    }
    .search-box input {
      width: 250px;
      padding: 8px 12px;
      background: transparent;
      border: 2px solid #0f3460;
      border-radius: 8px;
      color: #000000ff;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    .search-box input::placeholder {
      color: #555;
    }
    .search-box input:focus {
      outline: none;
      border-color: #00fff0;
      box-shadow: 0 0 8px rgba(0,255,240,0.6);
    }

    /* ─── 3) Mensaje de error ───────────────────────────────── */
    .error {
      background-color: rgba(255,75,75,0.2);
      color: #ff6b6b;
      border: 1px solid #ff6b6b;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 20px;
    }

    /* ─── 4) Tabla de productos ─────────────────────────────── */
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #16213e;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 0 12px rgba(0,255,240,0.2);
      color: #eaeaea;
    }
    th, td {
      padding: 12px 10px;
      text-align: left;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    th {
      background-color: #0f3460;
      color: #00fff0;
    }
    tr:last-child td {
      border-bottom: none;
    }

    /* ─── 5) Iconos de acción ──────────────────────────────── */
.actions {
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: flex-start;
}

.actions a {
  font-size: 1.2rem;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}

.actions .edit   { color: #08d9d6; }
.actions .delete { color: #ff6b6b; }


  </style>
</head>
<body>

<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
  <h1>Listado de Ventas</h1>
  <p style="color:#a0cfe8; margin-bottom:18px;">Las ventas se consideran como salidas de inventario y se registran en el kardex para mantener trazabilidad.</p>

  <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=addSaleAdminForm" class="button">
     Registrar Venta
  </a>
  <br>
  <br>

  <?php if (!empty($ventas)): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Empleado</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
          <th>Total</th>
          <th>Descripción</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ventas as $v): ?>
          <tr>
            <td><?php echo $v->id; ?></td>
            <td><?php echo htmlspecialchars($v->user_name); ?></td>
            <td><?php echo htmlspecialchars($v->product_name); ?></td>
            <td><?php echo $v->quantity; ?></td>
            <td>S/. <?php echo number_format($v->unit_price, 2, '.', ','); ?></td>
            <td>S/. <?php echo number_format($v->total_price, 2, '.', ','); ?></td>
            <td><?php echo htmlspecialchars($v->description); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($v->sale_date)); ?></td>
            <td><div class="actions">
                <a 
                  href="<?php echo BASE_URL; ?>index.php?controller=admin&action=editSaleAdminForm&id=<?php echo $v->id; ?>" 
                  class="edit"
                  title="Editar Venta"
                >✏️</a>
                <a 
                  href="<?php echo BASE_URL; ?>index.php?controller=admin&action=deleteSaleAdmin&id=<?php echo $v->id; ?>" 
                  class="delete"
                  title="Eliminar Venta"
                  onclick="return confirm('¿Seguro deseas eliminar esta venta? Esto devolverá el stock.');"
                >🗑️</a>
              </div>
            </td>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="summary" style="margin-top:18px; color:#a0fdfd;">
      <strong>Ganancia del día (<?php echo date('d-m-Y'); ?>):</strong>
      S/. <?php echo number_format($totalHoy, 2, '.', ','); ?>
    </div>
  <?php else: ?>
    <div class="no-data">
      <p>No hay ventas registradas aún.</p>
    </div>
  <?php endif; ?>
</div>

  <!-- Chatbot Widget -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
  <script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
