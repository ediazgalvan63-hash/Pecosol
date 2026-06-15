<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['username'] ?? 'Empleado';
$role = $_SESSION['role'] ?? 'employee';
$roleLabelMap = [
  'employee' => 'Empleado',
  'comercial' => 'Comercial',
  'logistica' => 'Logística',
  'finanzas' => 'Finanzas',
  'estrategico' => 'Estratégico',
  'gerencia' => 'Gerencia',
];
$roleLabel = $roleLabelMap[$role] ?? ucfirst($role);

$homeHref = BASE_URL . 'index.php?controller=dashboard&action=home';
$links = [];
$links[] = ['Dashboard', $homeHref];
if (in_array($role, ['employee', 'comercial'], true)) {
  $links[] = ['Registrar Venta', BASE_URL . 'index.php?controller=employee&action=addSaleForm'];
  $links[] = ['Mis Ventas', BASE_URL . 'index.php?controller=employee&action=listSalesEmployee'];
  $links[] = ['Productos', BASE_URL . 'index.php?controller=employee&action=listProductsEmployee'];
  if ($role === 'comercial') {
    $links[] = ['Órdenes', BASE_URL . 'index.php?controller=dashboard&action=logisticsWorkOrders'];
  }
} elseif ($role === 'logistica') {
  $links[] = ['Inventario', BASE_URL . 'index.php?controller=dashboard&action=logisticsInventory'];
  $links[] = ['Reconteo', BASE_URL . 'index.php?controller=dashboard&action=logisticsRecount'];
  $links[] = ['Compras', BASE_URL . 'index.php?controller=dashboard&action=logisticsPurchases'];
  $links[] = ['Órdenes', BASE_URL . 'index.php?controller=dashboard&action=logisticsWorkOrders'];
} elseif ($role === 'supervisor') {
  $links[] = ['Ventas', BASE_URL . 'index.php?controller=admin&action=listSalesAdmin'];
  $links[] = ['Productos', BASE_URL . 'index.php?controller=admin&action=listProducts'];
  $links[] = ['Alertas', BASE_URL . 'index.php?controller=dashboard&action=supervisorLowStockAlerts'];
  $links[] = ['Inventario', BASE_URL . 'index.php?controller=dashboard&action=logisticsInventory'];
  $links[] = ['Compras', BASE_URL . 'index.php?controller=dashboard&action=logisticsPurchases'];
  $links[] = ['Órdenes', BASE_URL . 'index.php?controller=dashboard&action=logisticsWorkOrders'];
  $links[] = ['Reconteo', BASE_URL . 'index.php?controller=dashboard&action=logisticsRecount'];
  $links[] = ['Reportes', BASE_URL . 'index.php?controller=dashboard&action=supervisorReports'];
} elseif ($role === 'finanzas') {
  $links[] = ['Ventas', BASE_URL . 'index.php?controller=dashboard&action=financeSales'];
  $links[] = ['Reportes', BASE_URL . 'index.php?controller=dashboard&action=financeReports'];
} elseif ($role === 'estrategico') {
  $links[] = ['Reportes', BASE_URL . 'index.php?controller=dashboard&action=strategyReports'];
} elseif ($role === 'gerencia') {
  $links[] = ['Reportes', BASE_URL . 'index.php?controller=dashboard&action=managementReports'];
}
?>

<style>
  :root {
    --bg: #16213e;
    --accent: #00fff0;
    --light: #eaeaea;
    --muted: #a0a0a0;
    --danger: #ff6b6b;
  }

  /* Asegurar que no haya decoraciones de texto problemáticas */
  header.employee * {
    text-decoration: none !important;
  }

  header.employee {
    width: 100%;
    background-color: var(--bg);
    border-bottom: 2px solid var(--accent);
    box-shadow: 0 4px 12px rgba(0,255,240,0.10);
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .emp-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 48px;
    flex-wrap: wrap;
  }

  .emp-brand {
    display: flex;
    align-items: center;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.6rem;
    color: var(--accent);
    gap: 12px;
  }

  .emp-brand img {
    height: 42px;
    transition: transform 0.3s ease;
  }

  .emp-brand:hover img {
    transform: scale(1.05);
  }

  .emp-brand small {
    color: var(--light);
    font-size: 1rem;
    font-weight: 400;
    margin-left: 6px;
  }

  .emp-links {
    display: flex;
    gap: 32px;
    align-items: center;
    flex-wrap: wrap;
  }

  .emp-toggle {
    display: none;
    background: transparent;
    border: 1px solid rgba(191,234,255,0.06);
    color: var(--light);
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 1.1rem;
    cursor: pointer;
  }

  .emp-links a {
    color: var(--light);
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    padding: 8px 4px;
    transition: all 0.3s ease;
    position: relative;
    white-space: nowrap;
  }

  .emp-links a::after {
    content: '';
    display: block;
    height: 2px;
    background-color: transparent;
    width: 0%;
    margin-top: 4px;
    transition: background-color 0.3s, width 0.3s;
  }

  .emp-links a:hover::after {
    width: 100%;
    background-color: var(--accent);
  }

  .emp-user {
    font-size: 0.95rem;
    color: var(--muted);
  }

  .emp-user strong {
    color: var(--accent);
  }

  .emp-logout {
    background-color: var(--danger);
    color: var(--bg);
    padding: 8px 16px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: bold;
    font-size: 0.9rem;
    transition: background-color 0.3s;
  }

  .emp-logout:hover {
    background-color: #ff4d4d;
  }

  @media(max-width: 900px) {
    .emp-nav {
      flex-direction: column;
      align-items: flex-start;
      padding: 16px 24px;
    }

    .emp-links {
      flex-direction: row;
      align-items: center;
      gap: 16px;
      width: 100%;
      margin-top: 12px;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 8px;
      scrollbar-width: thin;
    }

    /* On small screens, hide links behind toggle and show vertical list when opened */
    @media(max-width: 600px) {
      .emp-toggle { display: block; margin-left: 6px; }
      .emp-links { display: none; flex-direction: column; gap: 8px; margin-top: 8px; }
      .emp-nav.open .emp-links { display: flex; }
      .emp-links a { padding: 8px 10px; }
    }

    .emp-links::-webkit-scrollbar {
      height: 8px;
    }

    .emp-links::-webkit-scrollbar-thumb {
      background: rgba(0, 255, 240, 0.25);
      border-radius: 4px;
    }

    .emp-user, .emp-logout {
      margin-top: 0;
    }

    .user {
      margin-left: auto;
      display: flex;
      gap: 10px;
      flex-wrap: nowrap;
      align-items: center;
    }
  }
</style>

<header class="employee">
  <nav class="emp-nav">
    <!-- Branding con ícono -->
    <a href="<?php echo $homeHref; ?>" class="emp-brand">
      <img src="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" alt="Logo Pecosol">
      Pecosol <small><?php echo htmlspecialchars($roleLabel); ?></small>
    </a>

    <!-- Enlaces y usuario -->
    <button class="emp-toggle" aria-label="Abrir menú">☰</button>
    <div class="emp-links">
      <?php foreach ($links as $lnk): ?>
        <a href="<?php echo $lnk[1]; ?>"><?php echo htmlspecialchars($lnk[0]); ?></a>
      <?php endforeach; ?>
      <a href="<?php echo BASE_URL; ?>index.php?controller=employee&action=profile">👤 Mi Perfil</a>
  
</div>
<div class="user">
<span class="emp-user">Hola, <strong><?php echo htmlspecialchars($username); ?></strong></span>
  <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=logout" class="emp-logout">Cerrar Sesión</a>
</div>
  </nav>
</header>
<script>
  window.CHATBOT_API_URL = <?php echo json_encode(CHATBOT_API_URL, JSON_UNESCAPED_SLASHES); ?>;
  window.CHATBOT_USER_ID = <?php echo json_encode($_SESSION['user_id'] ?? null, JSON_UNESCAPED_SLASHES); ?>;
</script>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css" />
<script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js"></script>
<script>
  (function(){
    const toggle = document.querySelector('.emp-toggle');
    const nav = document.querySelector('.emp-nav');
    if (toggle && nav) {
      toggle.addEventListener('click', function(){ nav.classList.toggle('open'); });
      // close when clicking outside
      document.addEventListener('click', function(e){ if (!nav.contains(e.target) && nav.classList.contains('open')) nav.classList.remove('open'); });
    }
  })();
</script>
<script>
  // Assign data-labels to table cells from their thead headers on small screens
  (function(){
    function applyDataLabels() {
      try{
        var tables = document.querySelectorAll('.page-shell table, table.dashboard-table, table#productsTable');
        tables.forEach(function(tbl){
          var ths = Array.from(tbl.querySelectorAll('thead th')).map(th=>th.textContent.trim());
          if(ths.length===0) return;
          tbl.querySelectorAll('tbody tr').forEach(function(tr){
            Array.from(tr.children).forEach(function(td, i){
              if(!td.getAttribute('data-label')) td.setAttribute('data-label', ths[i] || '');
            });
          });
        });
      }catch(e){console.warn('applyDataLabels error', e);}
    }
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', applyDataLabels);
    else applyDataLabels();
    window.addEventListener('resize', applyDataLabels);
  })();
</script>