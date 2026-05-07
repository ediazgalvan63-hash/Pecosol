<?php
// views/employee/productos/list_products.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Productos Disponibles | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link
    rel="icon"
    href="<?php echo BASE_URL; ?>/assets/img/LogoPecosol.png"
    type="image/png"
  />

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
      max-width: 1000px;
      margin: 50px auto;
      padding: 0 20px;
      position: relative;
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #00fff0;
      font-size: 2rem;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 20px;
      gap: 12px;
    }

    .btn-back {
      display: inline-block;
      padding: 10px 16px;
      background-color: #0f3460;
      color: #00fff0;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-back:hover {
      background-color: #16213e;
      transform: translateY(-2px);
    }

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


    .suggestions {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background-color: #16213e;
      border: 1px solid #00fff0;
      border-top: none;
      max-height: 150px;
      overflow-y: auto;
      z-index: 10;
      display: none;
    }

    .suggestion-item {
      padding: 10px;
      cursor: pointer;
      color: #eaeaea;
    }

    .suggestion-item:hover {
      background-color: #0f3460;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #16213e;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 0 12px rgba(0,255,240,0.1);
    }

    th, td {
      padding: 14px 12px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      text-align: left;
    }

    th {
      background-color: #0f3460;
      color: #00fff0;
      font-weight: 600;
    }

    td {
      color: #eaeaea;
    }

    tr:last-child td {
      border-bottom: none;
    }

    p.no-products {
      color: #a0a0a0;
      font-style: italic;
      margin-top: 16px;
    }

    @media(max-width: 768px) {
      table {
        display: block;
        overflow-x: auto;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/../partials/header.php'; ?>

  <div class="container">
    <h1>Productos Disponibles</h1>

    <!-- Barra superior con botón y buscador -->
    <div class="top-bar">
      <?php
      $homeHref = BASE_URL . 'index.php?controller=dashboard&action=';
      switch ($_SESSION['role'] ?? 'employee') {
          case 'gerencia':
              $homeHref .= 'managementHome';
              break;
          case 'employee':
              $homeHref .= 'employeeHome';
              break;
          case 'comercial':
              $homeHref .= 'commercialHome';
              break;
          case 'estrategico':
              $homeHref .= 'strategyHome';
              break;
          default:
              $homeHref .= 'employeeHome';
              break;
      }
      ?>
      <a href="<?php echo $homeHref; ?>" class="btn-back">
        ← Volver al Dashboard
      </a>

      <div class="search-box">
        <input
          type="text"
          id="searchInput"
          placeholder="Buscar producto..."
          oninput="handleInput()"
        >
        <div id="suggestions" class="suggestions"></div>
      </div>
    </div>

    <?php if (!empty($productos)): ?>
      <table id="productsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio (S/.)</th>
            <th>Stock</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($productos as $prod): ?>
            <?php if ($prod->stock > 0): ?>
              <tr>
                <td><?php echo $prod->id; ?></td>
                <td class="prod-name"><?php echo htmlspecialchars($prod->name); ?></td>
                <td class="prod-desc"><?php echo htmlspecialchars($prod->description); ?></td>
                <td><?php echo number_format($prod->price, 2, '.', ','); ?></td>
                <td><?php echo $prod->stock; ?></td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="no-products">No hay productos registrados actualmente.</p>
    <?php endif; ?>
  </div>

  <script>
    const productNames = <?php echo json_encode(array_column($productos, 'name')); ?>;

    const input = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');
    const rows = document.querySelectorAll('#productsTable tbody tr');

    function handleInput() {
      const val = input.value.trim().toLowerCase();

      rows.forEach(row => {
        const name = row.querySelector('.prod-name').textContent.toLowerCase();
        const desc = row.querySelector('.prod-desc').textContent.toLowerCase();
        row.style.display = (name.includes(val) || desc.includes(val)) ? '' : 'none';
      });

      if (!val) {
        suggestionsBox.style.display = 'none';
        return;
      }

      const matches = productNames
        .filter(n => n.toLowerCase().includes(val))
        .slice(0, 5);
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

    document.addEventListener('click', e => {
      if (!e.target.closest('.search-box')) {
        suggestionsBox.style.display = 'none';
      }
    });
  </script>

</body>
</html>
