<?php
// views/admin/employee/edit_employee.php

// Variables disponibles desde AdminController::editEmployeeForm():
//   $empleado  (objeto) con propiedades: id, username, full_name, email, role
//   $error     (string) opcional: mensaje de validación o error
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar Empleado | Pecosol</title>
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
    margin: 40px auto;
    padding: 0 20px;
  }

  h1 {
    text-align: center;
    margin-bottom: 30px;
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
  input[type="email"],
  input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border: 1px solid #0f3460;
    border-radius: 6px;
    background-color: #1a1a2e;
    color: #eaeaea;
    box-sizing: border-box;
  }

  input::placeholder {
    color: #777;
  }

  input:focus {
    outline: none;
    border-color: #00fff0;
    box-shadow: 0 0 8px rgba(0,255,240,0.3);
  }

  /* ─── Checkbox ─────────────────────────────────────────────── */
  .checkbox-container {
    margin-top: 12px;
    display: flex;
    align-items: center;
    color: #eaeaea;
  }

  .checkbox-container input[type="checkbox"] {
    margin-right: 6px;
  }

  /* ─── Botón de enviar ─────────────────────────────────────── */
  .btn-submit {
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

  .btn-submit:hover {
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
    margin-bottom: 20px;
  }

  .back-link:hover {
    background: rgba(0, 255, 240, 0.3);
    color: #1a1a2e;
  }
</style>

  <script>
    // Mostrar/ocultar campo de nueva contraseña según checkbox
    function togglePasswordField() {
      var checkbox = document.getElementById('change_password');
      var pwdField = document.getElementById('new_password_container');
      pwdField.style.display = checkbox.checked ? 'block' : 'none';
    }
    window.addEventListener('DOMContentLoaded', function() {
      // Inicializar estado del campo de contraseña
      togglePasswordField();
      // Asignar evento al checkbox
      document.getElementById('change_password').addEventListener('change', togglePasswordField);
    });
  </script>
</head>
<body>

  <?php include __DIR__ . '/../partials/header.php'; ?>

  <div class="container">
    <h1>Editar Empleado</h1>

    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=listEmployees" class="back-link">
      ← Volver a Listado de Empleados
    </a>

    <div class="form-card">
      <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="index.php?controller=admin&action=updateEmployee" method="post">
        <!-- Campo oculto con el ID -->
        <input type="hidden" name="id" value="<?php echo $empleado->id; ?>">

        <label for="username">Usuario (no modificable):</label>
        <input
          type="text"
          id="username"
          name="username"
          value="<?php echo htmlspecialchars($empleado->username); ?>"
          readonly
        >

        <label for="full_name">Nombre Completo:</label>
        <input
          type="text"
          id="full_name"
          name="full_name"
          value="<?php echo htmlspecialchars($empleado->full_name); ?>"
          required
        >

        <label for="email">Email:</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?php echo htmlspecialchars($empleado->email); ?>"
          required
        >

        <label for="role">Rol:</label>
        <select id="role" name="role" required>
          <option value="comercial"<?php echo ($empleado->role === 'comercial') ? ' selected' : ''; ?>>Comercial</option>
          <option value="logistica"<?php echo ($empleado->role === 'logistica') ? ' selected' : ''; ?>>Logística</option>
          <option value="supervisor"<?php echo ($empleado->role === 'supervisor') ? ' selected' : ''; ?>>Supervisor</option>
          <option value="gerencia"<?php echo ($empleado->role === 'gerencia') ? ' selected' : ''; ?>>Gerencia</option>
          <option value="finanzas"<?php echo ($empleado->role === 'finanzas') ? ' selected' : ''; ?>>Finanzas</option>
          <option value="estrategico"<?php echo ($empleado->role === 'estrategico') ? ' selected' : ''; ?>>Estratégico</option>
          <option value="admin"<?php echo ($empleado->role === 'admin') ? ' selected' : ''; ?>>Administrador</option>
        </select>

        <div class="checkbox-container">
          <input
            type="checkbox"
            id="change_password"
            name="change_password"
          >
          <label for="change_password" style="margin: 0;">Cambiar Contraseña</label>
        </div>

        <div id="new_password_container" style="display: none;">
          <label for="new_password">Nueva Contraseña:</label>
          <input
            type="password"
            id="new_password"
            name="new_password"
            placeholder="Ingrese la nueva contraseña"
          >
        </div>

        <button type="submit" class="btn-submit">Actualizar Empleado</button>
      </form>
    </div>
  </div>

</body>
</html>
