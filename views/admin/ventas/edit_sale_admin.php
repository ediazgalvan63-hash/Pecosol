<?php
// views/admin/ventas/edit_sale_admin.php
// Variables disponibles:
//   $venta       (objeto con id, user_id, product_id, quantity, unit_price, total_price, description, sale_date, current_stock)
//   $empleados   (array de objetos con id, full_name)
//   $productos   (array de objetos con id, name, price, stock)
//   $error       (mensaje de validación, opcional)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Venta | Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link
    rel="icon"
    href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png"
    type="image/png"
  />


  <style>
  /* ─── Fondo y Tipografía Global ───────────────────────────── */
  body {
  background-color: #1a1a2e;
  background-image: url('<?php echo BASE_URL; ?>assets/img/overlapping-circles.svg');
  background-repeat: repeat;
  background-size: 60px;
  background-attachment: fixed;
}


  /* ─── Contenedor ──────────────────────────────────────────── */
  .container {
    max-width: 600px;
    margin: 60px auto;
    padding: 0 20px;
  }

  h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #00fff0;
  }

  /* ─── Tarjeta de formulario ───────────────────────────────── */
  .form-card {
    background-color: #16213e;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0, 255, 240, 0.1);
  }

  label {
    display: block;
    margin-top: 12px;
    color: #eaeaea;
  }

  select,
  input[type="number"],
  textarea {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border: 1px solid #0f3460;
    border-radius: 6px;
    background-color: #1a1a2e;
    color: #eaeaea;
    box-sizing: border-box;
  }

  input::placeholder,
  textarea::placeholder {
    color: #777;
  }

  select:focus,
  input:focus,
  textarea:focus {
    outline: none;
    border-color: #00fff0;
    box-shadow: 0 0 8px rgba(0, 255, 240, 0.3);
  }

  /* ─── Botón principal ─────────────────────────────────────── */
  button {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    background-color: #08d9d6;
    color: #1a1a2e;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s;
  }

  button:hover {
    background-color: #00fff0;
  }

  /* ─── Enlace de retorno ───────────────────────────────────── */
  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    font-size: 14px;
    font-weight: 500;
    color: #00fff0;
    background: rgba(0, 255, 240, 0.08);
    border: 1px solid rgba(0, 255, 240, 0.3);
    border-radius: 30px;
    text-decoration: none;
    box-shadow: 0 0 6px rgba(0, 255, 240, 0.1);
    backdrop-filter: blur(6px);
    transition: all 0.25s ease-in-out;
    margin-bottom: 20px;
  }

  .back-link:hover {
    background: rgba(0, 255, 240, 0.3);
    color: #1a1a2e;
  }

  /* ─── Texto informativo ───────────────────────────────────── */
  .info-text {
    margin-top: 15px;
    font-size: 0.9em;
    color: #aaa;
  }

  /* ─── Error ───────────────────────────────────────────────── */
  .error {
    background-color: rgba(255, 75, 75, 0.2);
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
  }
</style>

</head>
<body>

<?php
$role = $_SESSION['role'] ?? '';
$useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia'], true);
if ($useEmployeeHeader) {
    include __DIR__ . '/../../employee/partials/header.php';
} else {
    include __DIR__ . '/../partials/header.php';
}
$backUrl = $role === 'finanzas'
    ? BASE_URL . 'index.php?controller=dashboard&action=financeSales'
    : BASE_URL . 'index.php?controller=admin&action=listSalesAdmin';
?>

<div class="container">
  <h1>Editar Venta</h1>

  <a href="<?php echo $backUrl; ?>" class="back-link">
    ← Volver al Listado de Ventas
  </a>

  <div class="form-card">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>index.php?controller=admin&action=updateSaleAdmin" method="post">
      <!-- Campo oculto con el ID de la venta -->
      <input type="hidden" name="id" value="<?php echo $venta->id; ?>">

      <label for="user_id">Empleado:</label>
      <select id="user_id" name="user_id" required>
        <?php foreach ($empleados as $emp): ?>
          <option 
            value="<?php echo $emp->id; ?>" 
            <?php echo ($emp->id === $venta->user_id) ? 'selected' : ''; ?>
          >
            <?php echo htmlspecialchars($emp->full_name); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="product_id">Producto:</label>
      <select id="product_id" name="product_id" required>
        <?php foreach ($productos as $prod): ?>
          <option 
            value="<?php echo $prod->id; ?>"
            <?php echo ($prod->id === $venta->product_id) ? 'selected' : ''; ?>
          >
            <?php 
              echo htmlspecialchars($prod->name)
                . " (S/. " . number_format($prod->price, 2, '.', ',') 
                . " | Stock: " . $prod->stock . ")";
            ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="quantity">Cantidad:</label>
      <input
        type="number"
        id="quantity"
        name="quantity"
        min="1"
        step="1"
        value="<?php echo $venta->quantity; ?>"
        required
      >

      <label for="client_name">Cliente:</label>
      <input
        type="text"
        id="client_name"
        name="client_name"
        maxlength="120"
        value="<?php echo htmlspecialchars($venta->client_name ?? ''); ?>"
        required
      >

      <label for="description">Descripción (opcional):</label>
      <textarea
        id="description"
        name="description"
        rows="3"
      ><?php echo htmlspecialchars($venta->description); ?></textarea>

      <button type="submit">Actualizar Venta</button>
    </form>

    <p class="info-text">
      <strong>Fecha de venta:</strong> 
      <?php echo formatSaleDate($venta->sale_date, 'Y-m-d H:i'); ?>
    </p>
    <p class="info-text">
      <strong>Stock actual del producto antes de editar:</strong> 
      <?php echo $venta->current_stock; ?>
    </p>
  </div>
</div>

</body>
</html>
