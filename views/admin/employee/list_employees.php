<?php
// views/admin/empleados/list_employees.php
// Variables disponibles:
//   $empleados (array de objetos con id, username, full_name, email)
// Puede haber mensajes de error en:
//   $_SESSION['error_employee_delete']
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Listado de Empleados | Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">

    <link
    rel="icon"
    href="<?php echo BASE_URL; ?>/assets/img/LogoPecosol.png"
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
      .search-box input {
  width: 280px;
  padding: 10px 14px;
  background-color: rgba(0, 255, 240, 0.08);
  border: 2px solid #00fff0;
  border-radius: 10px;
  color: #eaeaea;
  font-size: 15px;
  font-weight: bold;
  box-shadow: 0 0 12px rgba(0, 255, 240, 0.2);
  transition: border-color 0.3s, box-shadow 0.3s, background-color 0.3s;
}

.search-box input::placeholder {
  color: #a0fdfd;
  opacity: 0.9;
}

.search-box input:focus {
  outline: none;
  border-color: #00fff0;
  background-color: rgba(0, 255, 240, 0.12);
  box-shadow: 0 0 16px rgba(0, 255, 240, 0.5);
}
.search-box {
  margin-bottom: 20px;
  text-align: right;
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
    .actions a {
      margin-right: 8px;
      font-size: 1.1rem;
    }
    .actions .edit   { color: #08d9d6; }
    .actions .delete { color: #ff6b6b; }
  </style>
</head>
<body>

  <!-- Header Admin -->
  <?php include __DIR__ . '/../partials/header.php'; ?>

  <div class="container">
    <h1>Listado de Empleados</h1>

    <!-- Botón Agregar Empleado -->
    <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="index.php?controller=admin&action=addEmployeeForm" class="button">
      Agregar Empleado
    </a>
    </div>

    <!-- Mensaje de error al eliminar -->
    <?php if (!empty($_SESSION['error_employee_delete'])): ?>
      <div class="error">
        <?php 
          echo htmlspecialchars($_SESSION['error_employee_delete']);
          unset($_SESSION['error_employee_delete']);
        ?>
      </div>
    <?php endif; ?>

    <!-- Buscador con autocomplete alineado a la derecha -->
    <div class="search-box">
      <input
        type="text"
        id="searchInput"
        placeholder="Buscar empleado..."
        oninput="handleInput()"
      >
      <div id="suggestions" class="suggestions"></div>
    </div>

    <!-- Tabla de Empleados -->
    <?php if (!empty($empleados)): ?>
      <table id="employeesTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Nombre Completo</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Fecha y Hora Creación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($empleados as $emp): ?>
            <tr>
              <td class="emp-id"><?php echo $emp->id; ?></td>
              <td class="emp-username"><?php echo htmlspecialchars($emp->username); ?></td>
              <td class="emp-fullname"><?php echo htmlspecialchars($emp->full_name); ?></td>
              <td class="emp-email"><?php echo htmlspecialchars($emp->email); ?></td>
              <td><?php echo htmlspecialchars(ucfirst($emp->role)); ?></td>
              <td><?php echo (isset($emp->created_at) && $emp->created_at) ? date('d/m/Y H:i', strtotime($emp->created_at)) : '-'; ?></td>
              <td class="actions">
                <a 
                  href="index.php?controller=admin&action=editEmployeeForm&id=<?php echo $emp->id; ?>" 
                  class="edit"
                  title="Editar"
                >✏️</a>
                <a 
                  href="index.php?controller=admin&action=deleteEmployee&id=<?php echo $emp->id; ?>" 
                  class="delete"
                  title="Eliminar"
                  onclick="return confirm('¿Estás seguro de eliminar este empleado?');"
                >🗑️</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No hay empleados registrados aún.</p>
    <?php endif; ?>
  </div>

  <script>
    // Array de usernames para sugerencias
    const employeeNames = <?php echo json_encode(array_column($empleados, 'username')); ?>;

    const input = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');
    const rows = document.querySelectorAll('#employeesTable tbody tr');

    function handleInput() {
      const val = input.value.trim().toLowerCase();

      // Filtrar tabla
      rows.forEach(row => {
        const id    = row.querySelector('.emp-id').textContent.toLowerCase();
        const user  = row.querySelector('.emp-username').textContent.toLowerCase();
        const name  = row.querySelector('.emp-fullname').textContent.toLowerCase();
        const mail  = row.querySelector('.emp-email').textContent.toLowerCase();
        const match = id.includes(val) || user.includes(val) || name.includes(val) || mail.includes(val);
        row.style.display = match ? '' : 'none';
      });

      // Autocomplete de username
      if (!val) {
        suggestionsBox.style.display = 'none';
        return;
      }
      const matches = employeeNames.filter(n => n.toLowerCase().includes(val)).slice(0, 5);
      renderSuggestions(matches);
    }

    function renderSuggestions(list) {
      suggestionsBox.innerHTML = '';
      if (list.length === 0) {
        suggestionsBox.style.display = 'none';
        return;
      }
      list.forEach(item => {
        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.textContent = item;
        div.onclick = () => {
          input.value = item;
          handleInput();
          suggestionsBox.style.display = 'none';
        };
        suggestionsBox.appendChild(div);
      });
      suggestionsBox.style.display = 'block';
    }

    // Cerrar sugerencias si haces clic fuera
    document.addEventListener('click', e => {
      if (!e.target.closest('.search-box')) {
        suggestionsBox.style.display = 'none';
      }
    });
  </script>
</body>
</html>
