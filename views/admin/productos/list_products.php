<?php
// views/admin/productos/list_products.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $_SESSION['error_product_delete'] ?? null;
unset($_SESSION['error_product_delete']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Listado de Productos | Administrador de Pecosol</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/img/LogoPecosol.png" type="image/png" />

    <!-- CSS de Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />

    <!-- Tu CSS general (incluye definición de .button) -->
    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>/assets/css/style.css"
    />

    <style>
        /* ─── 1) Fondo y tipografía ───────────────────────────── */
        body {
            background-color: #1a1a2e;
            background-image: url('<?php echo BASE_URL; ?>/assets/img/overlapping-circles.svg');
            background-repeat: repeat;
            background-size: 60px;
            background-attachment: fixed;
            color: #eaeaea;
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

        /* ─── 2) Buscador ───────────────────────────────────── */
        .search-box {
            margin-bottom: 20px;
            text-align: right;
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

        /* ─── 3) Mensaje de error ───────────────────────────── */
        .error {
            background-color: rgba(255,75,75,0.2);
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        /* ─── 4) Tabla de productos ─────────────────────────── */
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

        /* ─── 5) Íconos de acción ──────────────────────────── */
        .actions a {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        .actions .edit {
            color: #08d9d6;
        }
        .actions .delete {
            color: #ff6b6b;
        }
        .badge-alert {
            background: #ff6b6b;
            color: #1a1a2e;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 8px;
        }
    </style>
</head>
<body>

    <!-- Encabezado común -->
    <?php
    $role = $_SESSION['role'] ?? '';
    $useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'supervisor', 'finanzas', 'estrategico', 'gerencia'], true);
    if ($useEmployeeHeader) {
        include __DIR__ . '/../../employee/partials/header.php';
    } else {
        include __DIR__ . '/../partials/header.php';
    }
    ?>

    <div class="container">
        <h1>Listado de Productos</h1>
        <p style="color:#ffcccb; margin-top:-10px; margin-bottom:15px;">
            Productos con bajo stock: <?php echo (int)($lowStockCount ?? 0); ?>
        </p>

        <!-- Mensaje de error al eliminar -->
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Botón + buscador -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a
                href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=addProductForm"
                class="button"
            >Registrar</a>

            <div class="search-box">
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Buscar producto..."
                    oninput="handleInput()"
                />
            </div>
        </div>

        <!-- Tabla de productos -->
        <?php if (!empty($productos)): ?>
            <table id="productsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio (S/.)</th>
                        <th>Stock</th>
                        <th>Stock minimo</th>
                        <th>Fecha y Hora</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td><?php echo $prod->id; ?></td>
                            <td class="prod-name"><?php echo htmlspecialchars($prod->name); ?></td>
                            <td class="prod-desc"><?php echo htmlspecialchars($prod->description); ?></td>
                            <td><?php echo number_format($prod->price, 2, '.', ','); ?></td>
                            <td><?php echo (int)$prod->stock; ?></td>
                            <td><?php echo (int)($prod->stock_minimum ?? 0); ?></td>
                            <td><?php echo (isset($prod->created_at) && $prod->created_at) ? date('d/m/Y H:i', strtotime($prod->created_at)) : '-'; ?></td>
                            <td>
                                <?php if ((int)$prod->stock <= (int)($prod->stock_minimum ?? 0)): ?>
                                    <span class="badge-alert">Bajo stock</span>
                                <?php else: ?>
                                    <span style="color:#7dff7d;">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a
                                    href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=editProductForm&id=<?php echo $prod->id; ?>"
                                    class="edit"
                                    title="Editar"
                                >✏️</a>
                                <a
                                    href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=deleteProduct&id=<?php echo $prod->id; ?>"
                                    class="delete"
                                    title="Eliminar"
                                    onclick="return confirm('¿Seguro deseas eliminar este producto?');"
                                >🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#a0a0a0;">No hay productos registrados aún.</p>
        <?php endif; ?>
    </div>

    <!-- Script de búsqueda -->
    <script>
        const input     = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#productsTable tbody tr');
        function handleInput() {
            const val = input.value.trim().toLowerCase();
            tableRows.forEach(row => {
                const name = row.querySelector('.prod-name').textContent.toLowerCase();
                const desc = row.querySelector('.prod-desc').textContent.toLowerCase();
                row.style.display = (name.includes(val) || desc.includes(val)) ? '' : 'none';
            });
        }
    </script>

    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/js/bootstrap.bundle.min.js"
    ></script>
  <!-- Chatbot Widget -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
  <script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
