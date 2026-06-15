<?php
// views/admin/employee/add_employee.php

// Variable opcional: $error (mensaje de validación si falla storeEmployee)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Agregar Usuario | Admin</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link
    rel="icon"
    href="<?php echo BASE_URL; ?>/assets/img/LogoPecosol.png"
    type="image/png"
  />
  <style>
  /* ─── Fondo y Tipografía Global ───────────────────────────── */
  body {
    font-family: Arial, sans-serif;
    background-color: #1a1a2e;
    color: #eaeaea;
    margin: 0;
    padding: 0;
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
  input[type="password"],
  input[type="email"],
  select {
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

  input::placeholder,
  select {
    color: #777;
    background-color: #1a1a2e;
  }

  input:focus,
  select:focus {
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
</style>

</head>
<body>

  <?php include __DIR__ . '/../partials/header.php'; ?>

  <div class="container">
    <h1>Agregar Nuevo Usuario</h1>
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listEmployees" class="back-link">
      ← Volver a Listado de Usuarios
    </a>

    <div class="form-card">
      <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="index.php?controller=admin&action=storeEmployee" method="post">
        <label for="username">Usuario (username):</label>
        <input
          type="text"
          id="username"
          name="username"
          placeholder="Ej: empleado1"
          value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
          required
        >

        <label for="password">Contraseña:</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Contraseña para el usuario"
          required
        >

        <label for="full_name">Nombre Completo:</label>
        <input
          type="text"
          id="full_name"
          name="full_name"
          placeholder="Ej: Juan Pérez"
          value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
          required
        >

        <label for="email">Email:</label>
        <input
          type="email"
          id="email"
          name="email"
          placeholder="ejemplo@correo.com"
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
          required
        >

        <label for="role">Rol:</label>
        <select id="role" name="role" required>
          <option value="">-- Selecciona un rol --</option>
          <option value="comercial"<?php if(($_POST['role'] ?? '')==='comercial') echo ' selected'; ?>>Comercial</option>
          <option value="admin"<?php if(($_POST['role'] ?? '')==='admin') echo ' selected'; ?>>Administrador</option>
          <option value="supervisor"<?php if(($_POST['role'] ?? '')==='supervisor') echo ' selected'; ?>>Supervisor</option>
        </select>

        <button type="submit">Guardar Usuario</button>
      </form>
    </div>
  </div>

</body>
</html>
