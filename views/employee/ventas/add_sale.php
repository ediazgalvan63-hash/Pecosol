<?php
// views/employee/ventas/add_sale.php

$productosDisponibles = [];
foreach ($productos as $prod) {
    if ((int)$prod->stock > 0) {
        $productosDisponibles[] = $prod;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Venta | Empleado</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="icon" href="<?php echo BASE_URL; ?>/assets/img/LogoPecosol.png" type="image/png" />

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
      max-width: 500px;
      margin: 60px auto;
      padding: 0 20px;
    }

    h1 {
      text-align: center;
      color: #00fff0;
      margin-bottom: 30px;
      font-size: 1.8rem;
    }

    .form-card {
      background-color: #0f3460;
      border-radius: 16px;
      padding: 32px 28px;
      box-shadow: 0 0 20px rgba(0,255,240,0.1);
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
      margin-top: 20px;
      font-weight: 600;
      letter-spacing: 0.03em;
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
      box-shadow: inset 0 0 12px rgba(0, 255, 240, 0.08);
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

    input[type="text"]::placeholder,
    input[type="number"]::placeholder,
    textarea::placeholder {
      color: rgba(234, 234, 234, 0.55);
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    textarea:focus,
    select:focus {
      outline: none;
      border-color: #5ef3d4;
      box-shadow: 0 0 0 4px rgba(0, 255, 240, 0.12);
      transform: translateY(-1px);
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    button {
      margin-top: 24px;
      width: 100%;
      padding: 12px;
      background-color: #00fff0;
      color: #1a1a2e;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: bold;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    button:hover {
      background-color: #00cfc4;
      transform: scale(1.02);
    }

    .error {
      background-color: rgba(255, 0, 0, 0.1);
      color: #ff6b6b;
      border: 1px solid #ff6b6b;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
      font-weight: 500;
    }

    .note {
      font-size: 0.9rem;
      color: #a0a0a0;
      margin-top: 6px;
    }

    @media (max-width: 576px) {
      .form-card {
        padding: 24px 20px;
      }

      h1 {
        font-size: 1.5rem;
      }

      button {
        font-size: 0.95rem;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/../partials/header.php'; ?>

  <div class="container">
    <h1>Registrar Nueva Venta</h1>

    <div class="form-card">

      <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="index.php?controller=employee&action=storeSale" method="post">
        <label for="product_id">Producto:</label>
        <select name="product_id" id="product_id" required>
          <option value="">-- Selecciona un producto --</option>
          <?php foreach ($productosDisponibles as $prod): ?>
            <option value="<?php echo $prod->id; ?>">
              <?php
                $name  = str_pad(substr($prod->name, 0, 20), 20);
                $stock = str_pad("Stock: {$prod->stock}", 14);
                $price = "S/. " . number_format($prod->price, 2);
                echo "{$name} | {$stock} | {$price}";
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
          placeholder="Ingresa la cantidad"
          required
          value="<?php echo htmlspecialchars($quantity ?? ''); ?>"
        >
        <div class="note">Máximo disponible: revisa el stock mostrado en el dropdown.</div>

        <label for="client_name">Cliente:</label>
        <input
          type="text"
          id="client_name"
          name="client_name"
          autocomplete="name"
          placeholder="Nombre completo del cliente"
          required
          value="<?php echo htmlspecialchars($clientName ?? ''); ?>"
        >

        <label for="description">Descripción (opcional):</label>
        <textarea
          name="description"
          id="description"
          rows="3"
          placeholder="Agregar alguna observación (ej. Cliente especial, motivo, etc.)"
        ><?php echo htmlspecialchars($description ?? ''); ?></textarea>

        <button type="submit">Registrar Venta</button>
      </form>
    </div>
  </div>

</body>
</html>
