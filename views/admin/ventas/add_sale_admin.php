<?php
// views/admin/ventas/add_sale_admin.php
// Variables disponibles:
//   $empleados  (array de objetos con id, full_name)
//   $productos  (array de objetos con id, name, price, stock)
//   $error      (mensaje de validación, opcional)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Venta | Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
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

  /* ─── Tarjeta del formulario ──────────────────────────────── */
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
    font-size: 1rem;
    box-sizing: border-box;
  }

  select:focus,
  input[type="number"]:focus,
  textarea:focus {
    outline: none;
    border-color: #00fff0;
    box-shadow: 0 0 8px rgba(0,255,240,0.3);
  }

  /* ─── Botón ───────────────────────────────────────────────── */
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

  /* ─── Mensaje de error ────────────────────────────────────── */
  .error {
    background-color: rgba(255, 75, 75, 0.2);
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 12px;
    text-align: center;
  }

  /* ─── Enlace de volver ────────────────────────────────────── */
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
    margin-bottom: 15px;
  }

  .back-link:hover {
    background: rgba(0, 255, 240, 0.3);
    color: #1a1a2e;
  }
</style>

</head>
<body>

<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
  <h1>Registrar Nueva Venta</h1>

    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listSalesAdmin" style="
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
">
  <span style="font-size: 16px;">⟵</span> Volver al Listado de Ventas
</a>


  </a>

  <div class="form-card">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>index.php?controller=admin&action=storeSaleAdmin" method="post">
      <label for="user_id">Empleado:</label>
      <select id="user_id" name="user_id" required>
        <option value="">-- Selecciona un empleado --</option>
        <?php foreach ($empleados as $emp): ?>
          <option value="<?php echo $emp->id; ?>">
            <?php echo htmlspecialchars($emp->full_name); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="product_id">Producto:</label>
      <select id="product_id" name="product_id" required>
        <option value="">-- Selecciona un producto --</option>
        <?php foreach ($productos as $prod): ?>
          <?php if ($prod->stock > 0): ?>
            <option value="<?php echo $prod->id; ?>">
              <?php 
                echo htmlspecialchars($prod->name) 
                  . " (S/. " . number_format($prod->price, 2, '.', ',') 
                  . " | Stock: " . $prod->stock . ")";
              ?>
            </option>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>

      <label for="quantity">Cantidad:</label>
      <input
        type="number"
        id="quantity"
        name="quantity"
        min="1"
        step="1"
        placeholder="Ej: 3"
        required
      >

      <label for="client_name">Cliente:</label>
      <input
        type="text"
        id="client_name"
        name="client_name"
        maxlength="120"
        placeholder="Nombre del cliente"
        required
      >

      <label for="description">Descripción (opcional):</label>
      <textarea
        id="description"
        name="description"
        rows="3"
        placeholder="Comentarios sobre la venta"
      ></textarea>

      <button type="submit">Registrar Venta</button>
    </form>
  </div>
</div>

</body>
</html>
