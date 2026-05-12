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
    border-radius: 14px;
    padding: 32px 28px;
    box-shadow: 0 0 20px rgba(0, 255, 240, 0.1);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .form-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 25px rgba(0,255,240,0.15);
  }

  label {
    display: block;
    margin-top: 18px;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: #d2f7ff;
  }

  select,
  input[type="text"],
  input[type="number"],
  textarea {
    width: 100%;
    padding: 14px 16px;
    margin-top: 8px;
    border: 1px solid rgba(0, 255, 240, 0.35);
    border-radius: 14px;
    background-color: rgba(15, 23, 42, 0.95);
    color: #eaeaea;
    font-size: 1rem;
    box-sizing: border-box;
    box-shadow: inset 0 0 18px rgba(0, 255, 240, 0.08);
    transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease;
  }

  select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: linear-gradient(45deg, transparent 50%, #00fff0 50%),
                      linear-gradient(135deg, #00fff0 50%, transparent 50%);
    background-position: calc(100% - 20px) calc(1em + 2px), calc(100% - 15px) calc(1em + 2px);
    background-size: 5px 5px, 5px 5px;
    background-repeat: no-repeat;
  }

  select option {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 0.95rem;
    padding: 8px 10px;
    background-color: #0f172a;
    color: #eaeaea;
  }

  select:focus,
  input[type="text"]:focus,
  input[type="number"]:focus,
  textarea:focus {
    outline: none;
    border-color: #5ef3d4;
    box-shadow: 0 0 0 5px rgba(0, 255, 240, 0.12);
    transform: translateY(-1px);
  }

  input[type="text"]::placeholder,
  input[type="number"]::placeholder,
  textarea::placeholder {
    color: rgba(234, 234, 234, 0.55);
  }

  /* ─── Botón ───────────────────────────────────────────────── */
  button {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    background-color: #00fff0;
    color: #1a1a2e;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }

  button:hover {
    background-color: #00cfc4;
    transform: scale(1.02);
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

    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listSalesAdmin" class="back-link">
      <span style="font-size: 16px;">⟵</span> Volver al Listado de Ventas
    </a>

  <div class="form-card">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>index.php?controller=admin&action=storeSaleAdmin" method="post">
      <label for="user_id">Empleado (solo Admin o Comercial):</label>
      <select id="user_id" name="user_id" required>
        <option value="">-- Selecciona un empleado --</option>
        <?php foreach ($empleados as $emp): ?>
          <?php if (!in_array($emp->role, ['admin', 'comercial'], true)) continue; ?>
          <option value="<?php echo $emp->id; ?>">
            <?php echo htmlspecialchars($emp->full_name); ?> (<?php echo htmlspecialchars(ucfirst($emp->role)); ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <p class="note">Solo los roles administrador y comercial pueden registrar ventas.</p>

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
        autocomplete="name"
        maxlength="120"
        placeholder="Nombre completo del cliente"
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
