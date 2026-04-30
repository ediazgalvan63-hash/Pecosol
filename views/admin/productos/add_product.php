<?php
// views/admin/productos/add_product.php
// Variable opcional: $error (string) con mensaje de validación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Agregar Producto | Admin</title>
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
    max-width: 500px;
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

  input[type="text"],
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
    resize: vertical;
  }

  input::placeholder,
  textarea::placeholder {
    color: #777;
  }

  input:focus,
  textarea:focus {
    outline: none;
    border-color: #00fff0;
    box-shadow: 0 0 8px rgba(0,255,240,0.3);
  }

  /* ─── Botón de enviar ─────────────────────────────────────── */
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
    margin-bottom: 20px;
    text-align: center;
  }
</style>


</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container">
  <h1>Agregar Nuevo Producto</h1>

    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listProducts" style="
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
  <span style="font-size: 16px;">⟵</span> Volver a Listado de Productos
</a>

  

  <div class="form-card">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form action="index.php?controller=admin&action=storeProduct" method="post">
      <label for="name">Nombre del Producto:</label>
      <input
        type="text"
        id="name"
        name="name"
        placeholder="Ej: Producto X"
        required
      >

      <label for="description">Descripción:</label>
      <textarea
        id="description"
        name="description"
        rows="3"
        placeholder="Descripción breve (opcional)"
      ></textarea>

      <label for="price">Precio (S/.):</label>
      <input
        type="number"
        id="price"
        name="price"
        step="0.01"
        min="0.01"
        placeholder="Ej: 19.99"
        required
      >

      <label for="stock">Stock Inicial:</label>
      <input
        type="number"
        id="stock"
        name="stock"
        min="0"
        step="1"
        placeholder="Ej: 50"
        required
      >

      <label for="stock_minimum">Stock mínimo:</label>
      <input
        type="number"
        id="stock_minimum"
        name="stock_minimum"
        min="1"
        step="1"
        placeholder="Ej: 10"
        required
      >
      <small style="color:#a0fdfd; display:block; margin-bottom:16px;">Define un stock mínimo real mayor a cero para alertas de reabastecimiento.</small>

      <button type="submit">Guardar Producto</button>
    </form>
  </div>
</div>

</body>
</html>
