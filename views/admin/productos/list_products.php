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
        href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>"
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
        .d-flex {
            flex-wrap: wrap;
            gap: 10px;
        }
        .search-box {
            flex: 1 1 250px;
            min-width: 180px;
            text-align: right;
        }
        .search-box input {
            width: 100%;
            max-width: 320px;
        }
        @media (max-width: 780px) {
            .d-flex {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                width: 100%;
                text-align: left;
                margin-top: 8px;
            }
            .search-box input {
                max-width: 100%;
            }
            .button {
                width: 100%;
                justify-content: center;
            }
            table {
                display: block;
                overflow-x: auto;
                width: 100%;
            }
            thead {
                display: none;
            }
            tbody tr {
                display: block;
                margin-bottom: 14px;
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 12px;
                padding: 12px;
            }
            tbody td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 8px 0;
                border: none;
            }
            tbody td::before {
                content: attr(data-label);
                flex: 1 1 40%;
                color: #86f4ff;
                font-weight: 600;
                min-width: 120px;
            }
            tbody td:last-child {
                justify-content: flex-start;
            }
            .actions {
                flex-wrap: wrap;
            }
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
    <!-- Mobile-specific inline overrides and helper script (highest priority) -->
    <style>
        @media (max-width: 820px) {
            body.admin-panel .page-shell table#productsTable thead { display: none !important; }
            body.admin-panel .page-shell table#productsTable, body.admin-panel table.dashboard-table { display:block !important; width:100% !important; overflow:visible !important; }
            body.admin-panel .page-shell table#productsTable tbody tr { display:block !important; margin-bottom:12px !important; padding:12px !important; border-radius:10px !important; background: rgba(255,255,255,0.02) !important; }
            body.admin-panel .page-shell table#productsTable tbody td { display:flex !important; justify-content:space-between !important; gap:10px !important; padding:8px 0 !important; border:none !important; }
            body.admin-panel .page-shell table#productsTable tbody td::before { content: attr(data-label) !important; color:#86f4ff !important; font-weight:700 !important; min-width:110px !important; flex:0 0 40% !important; }
            .module { padding:10px !important; }
        }
    </style>
    <script>
        (function(){
            function setMobileState(){
                try{
                    if(window.innerWidth <= 820){
                        document.body.classList.add('mobile-ready');
                        document.body.classList.add('nav-collapsed');
                        var adminNav = document.getElementById('mainNavLinks');
                        if(adminNav && !adminNav.classList.contains('collapsed')) adminNav.classList.add('collapsed');
                        var empLinks = document.querySelector('.emp-links');
                        var empNav = document.querySelector('.emp-nav');
                        if(empLinks && empNav && !empNav.classList.contains('open')) empNav.classList.remove('open');
                    } else {
                        document.body.classList.remove('mobile-ready');
                        document.body.classList.remove('nav-collapsed');
                    }
                }catch(e){console.warn('mobile adapt error',e)}
            }
            setMobileState();
            window.addEventListener('resize', setMobileState);
            // Also collapse on initial user-agent mobile detection (safeguard)
            if(/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) setMobileState();
            // If device supports touch, also force mobile nav collapse
            if(('ontouchstart' in window) || (navigator.maxTouchPoints && navigator.maxTouchPoints>0)) {
                try{ document.body.classList.add('nav-collapsed');
                    var mn = document.getElementById('mainNavLinks'); if(mn) mn.classList.add('collapsed');
                    var en = document.querySelector('.emp-nav'); if(en) en.classList.remove('open');
                }catch(e){}
            }
        })();
    </script>
</head>
<?php $role = $_SESSION['role'] ?? 'admin'; ?>
<body class="<?php echo htmlspecialchars($role); ?>-panel">

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

        <main class="page-shell">
            <section class="module">
                <div class="container">
                        <div class="module-header">
                            <h1>Listado de Productos</h1>
                        </div>
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
            <table id="productsTable" class="dashboard-table">
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
                            <td data-label="ID"><?php echo $prod->id; ?></td>
                            <td class="prod-name" data-label="Nombre"><?php echo htmlspecialchars($prod->name); ?></td>
                            <td class="prod-desc" data-label="Descripción"><?php echo htmlspecialchars($prod->description); ?></td>
                            <td data-label="Precio (S/.)"><?php echo number_format($prod->price, 2, '.', ','); ?></td>
                            <td data-label="Stock"><?php echo (int)$prod->stock; ?></td>
                            <td data-label="Stock minimo"><?php echo (int)($prod->stock_minimum ?? 0); ?></td>
                            <td data-label="Fecha y Hora"><?php echo (isset($prod->created_at) && $prod->created_at) ? formatSaleDate($prod->created_at, 'd/m/Y H:i') : '-'; ?></td>
                            <td data-label="Estado">
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
      </section>
    </main>

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
</body>
</html>
