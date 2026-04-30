<?php
// views/admin/productos/edit_product.php
// Variable disponible: $producto (objeto con id, name, description, price, stock)
// Variable opcional: $error (mensaje de validación)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Producto | Admin</title>
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


  /* ─── Contenedor General ───────────────────────────────────── */
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

  /* ─── Formulario de edición ───────────────────────────────── */
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

  /* ─── Botón de acción ─────────────────────────────────────── */
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

  /* ─── Error ──────────────────────────────────────────────── */
  .error {
    background-color: rgba(255, 75, 75, 0.2);
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
  }

  /* ─── Buscador (reutilizable) ─────────────────────────────── */
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
    color: #eaeaea;
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

  /* ─── Tabla (por si se usa cerca del formulario) ──────────── */
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

  .actions a {
    margin-right: 8px;
    font-size: 1.1rem;
  }

  .actions .edit   { color: #08d9d6; }
  .actions .delete { color: #ff6b6b; }
</style>

</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="container">
  <h1>Editar Producto</h1>
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
  <span style="font-size: 16px;">⟵</span> Lista de productos
</a>

  <div class="form-card">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="index.php?controller=admin&action=updateProduct" method="post">
      <!-- Campo oculto para el ID del producto -->
      <input type="hidden" name="id" value="<?php echo $producto->id; ?>">

      <label for="name">Nombre del Producto:</label>
      <input
        type="text"
        id="name"
        name="name"
        value="<?php echo htmlspecialchars($producto->name); ?>"
        required
      >

      <label for="description">Descripción:</label>
      <textarea
        id="description"
        name="description"
        rows="3"
      ><?php echo htmlspecialchars($producto->description); ?></textarea>

      <label for="price">Precio (S/.):</label>
      <input
        type="number"
        id="price"
        name="price"
        step="0.01"
        min="0.01"
        value="<?php echo number_format((float)$producto->price, 2, '.', ''); ?>"
        required
      >

      <label for="stock">Stock:</label>
      <input
        type="number"
        id="stock"
        name="stock"
        min="0"
        step="1"
        value="<?php echo (int)$producto->stock; ?>"
        required
      >

      <label for="stock_minimum">Stock mínimo:</label>
      <input
        type="number"
        id="stock_minimum"
        name="stock_minimum"
        min="1"
        step="1"
        value="<?php echo (int)($producto->stock_minimum ?? 1); ?>"
        required
      >
      <small style="color:#a0fdfd; display:block; margin-bottom:16px;">Este valor debe ser mayor a cero para controlar inventarios críticos.</small>

      <button type="submit">Actualizar Producto</button>
    </form>
  </div>
</div>

</body>
</html>
