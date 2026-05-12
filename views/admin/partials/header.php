<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$username = $_SESSION['full_name'] ?? 'Usuario';

// Detectar controlador y acción actuales
$currentController = $_GET['controller'] ?? '';
$currentAction     = $_GET['action'] ?? '';

$tzDebug = '';
// Mostrar debug de TZ solo cuando se habilita explícitamente (evita verse "no profesional")
$showTzDebug = (getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1');
if ($showTzDebug && !empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
  $tzDebug = sprintf(
    'APP_TIMEZONE=%s | DB_TIMEZONE=%s | now=%s',
    defined('APP_TIMEZONE') ? APP_TIMEZONE : '(no APP_TIMEZONE)',
    defined('DB_TIMEZONE') ? DB_TIMEZONE : '(no DB_TIMEZONE)',
    date('Y-m-d H:i:s')
  );
}
?>

<!-- ==== STYLES ==== -->
<style>
  :root {
    --bg: #16213e;
    --accent: #00fff0;
    --light: #eaeaea;
    --muted: #a0a0a0;
    --danger: #ff6b6b;
  }

  header {
    width: 100%;
    background-color: var(--bg);
    border-bottom: 2px solid var(--accent);
    box-shadow: 0 4px 12px rgba(0,255,240,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
  }

  nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 48px;
    flex-wrap: wrap;
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    flex-shrink: 0;
    min-width: 240px;
  }

  .brand img {
    height: 44px;
  }

  .brand span {
    font-size: 1.6rem;
    font-weight: bold;
    color: var(--accent);
  }

  .brand span small {
    color: var(--light);
    font-size: 1rem;
    font-weight: 400;
    margin-left: 4px;
  }

  .nav-links {
    display: flex;
    gap: 26px;
    flex-grow: 1;
    justify-content: center;
    flex-wrap: wrap;
    align-items: center;
    padding: 6px 0;
  }

  .nav-links a {
    position: relative;
    color: var(--light);
    text-decoration: none;
    font-weight: 500;
    padding: 10px 6px;
    transition: all 0.3s ease;
  }

  .nav-links a::after {
    content: '';
    display: block;
    height: 2px;
    background-color: transparent;
    transition: background-color 0.3s, width 0.3s;
    width: 0%;
    margin-top: 4px;
  }

  .nav-links a:hover::after {
    width: 100%;
    background-color: var(--accent);
  }

  .nav-links a.active {
    color: var(--accent);
  }

  .nav-links a.active::after {
    background-color: var(--accent);
    width: 100%;
  }

  .user {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
  }

  .user span {
    color: var(--muted);
    font-size: 0.95rem;
  }

  .user strong {
    color: var(--accent);
  }

  .logout {
    padding: 10px 16px;
    background-color: var(--danger);
    color: var(--bg);
    text-decoration: none;
    border-radius: 30px;
    font-weight: bold;
    font-size: 0.9rem;
    transition: background-color 0.3s;
  }

  .logout:hover {
    background-color: #ff4d4d;
  }

  .menu-toggle {
    display: none;
    font-size: 28px;
    background: none;
    border: none;
    color: var(--accent);
    cursor: pointer;
  }

  @media(max-width: 900px) {
    nav {
      flex-direction: column;
      padding: 16px 24px;
      align-items: flex-start;
    }

    .nav-links {
      flex-direction: column;
      width: 100%;
      margin: 12px 0;
      gap: 16px;
    }

    .user {
      justify-content: flex-start;
      width: 100%;
      margin-bottom: 12px;
    }

    .menu-toggle {
      display: block;
      position: absolute;
      top: 16px;
      right: 24px;
    }

    .nav-links.collapsed {
      display: none;
    }
  }
</style>

<!-- ==== HEADER HTML ==== -->
<header>
  <?php if ($tzDebug !== ''): ?>
    <div style="padding:6px 48px; background:#0f172a; color:#9ae7ff; font-size:12px; border-bottom:1px solid rgba(0,255,240,0.15);">
      <?php echo htmlspecialchars($tzDebug); ?>
    </div>
  <?php endif; ?>
  <nav>
    <!-- Logo -->
    <a href="<?php echo BASE_URL; ?>?controller=dashboard&action=adminHome" class="brand">
      <!-- Asegurarse de usar la extensión del archivo. LogoPecosol.png existe en assets/img -->
      <img src="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" alt="Logo Pecosol" class="logo-img" />
      <span>Pecosol<small>Admin</small></span>
    </a>

    <!-- Menú hamburguesa -->
    <button class="menu-toggle" onclick="document.getElementById('mainNavLinks').classList.toggle('collapsed')">☰</button>

    <!-- Enlaces navegación -->
    <div class="nav-links" id="mainNavLinks">
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=listInventoryMovements"
         class="<?php echo ($currentController === 'admin' && in_array($currentAction, ['listInventoryMovements', 'addInventoryMovementForm'], true)) ? 'active' : ''; ?>">
        Inventario
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=inventoryRecountForm"
         class="<?php echo ($currentController === 'admin' && $currentAction === 'inventoryRecountForm') ? 'active' : ''; ?>">
        Reconteo
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=listPurchases"
         class="<?php echo ($currentController === 'admin' && in_array($currentAction, ['listPurchases', 'addPurchaseForm'], true)) ? 'active' : ''; ?>">
        Compras
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=listWorkOrders"
         class="<?php echo ($currentController === 'admin' && in_array($currentAction, ['listWorkOrders', 'addWorkOrderForm'], true)) ? 'active' : ''; ?>">
        Ordenes
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=lowStockAlerts"
         class="<?php echo ($currentController === 'admin' && $currentAction === 'lowStockAlerts') ? 'active' : ''; ?>">
        ⚠️ Alertas
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=listProducts"
         class="<?php echo ($currentController === 'admin' && $currentAction === 'listProducts') ? 'active' : ''; ?>">
        Productos
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=listSalesAdmin"
         class="<?php echo ($currentController === 'admin' && $currentAction === 'listSalesAdmin') ? 'active' : ''; ?>">
        Ventas
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=listEmployees"
         class="<?php echo ($currentController === 'admin' && $currentAction === 'listEmployees') ? 'active' : ''; ?>">
        Empleados
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=admin&action=reports"
         class="<?php echo ($currentController === 'admin' && $currentAction === 'reports') ? 'active' : ''; ?>">
        Reportes
      </a>
      <a href="<?php echo BASE_URL; ?>?controller=dashboard&action=adminHome"
         class="<?php echo ($currentController === 'dashboard' && $currentAction === 'adminHome') ? 'active' : ''; ?>">
        Dashboard
      </a>
    </div>

    <!-- Usuario -->
    <div class="user">
      <span>Bienvenido, <strong><?php echo htmlspecialchars($username); ?></strong></span>
      <a href="<?php echo BASE_URL; ?>?controller=employee&action=profile" style="margin-right: 10px; color: #00fff0; text-decoration: none;">👤 Mi Perfil</a>
      <a class="logout" href="<?php echo BASE_URL; ?>?controller=auth&action=logout">Cerrar sesión</a>
    </div>
  </nav>
</header>

<script>
  window.CHATBOT_API_URL = <?php echo json_encode(CHATBOT_API_URL, JSON_UNESCAPED_SLASHES); ?>;
  window.CHATBOT_USER_ID = <?php echo json_encode($_SESSION['user_id'] ?? null, JSON_UNESCAPED_SLASHES); ?>;
</script>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css" />
<script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js"></script>
