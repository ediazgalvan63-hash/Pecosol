<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['username'] ?? 'Empleado';
?>

<style>
  :root {
    --bg: #16213e;
    --accent: #00fff0;
    --light: #eaeaea;
    --muted: #a0a0a0;
    --danger: #ff6b6b;
  }

  header.employee {
    width: 100%;
    background-color: var(--bg);
    border-bottom: 2px solid var(--accent);
    box-shadow: 0 4px 12px rgba(0,255,240,0.1);
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

  .emp-links a {
    color: var(--light);
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    padding: 8px 4px;
    transition: all 0.3s ease;
    position: relative;
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
      flex-direction: column;
      align-items: flex-start;
      gap: 14px;
      width: 100%;
      margin-top: 12px;
    }

    .emp-user, .emp-logout {
      margin-top: 8px;
    }
  }
</style>

<header class="employee">
  <nav class="emp-nav">
    <!-- Branding con ícono -->
    <a href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=employeeHome" class="emp-brand">
      <img src="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" alt="LogoPecosol">
      Pecosol <small>Empleado</small>
    </a>

    <!-- Enlaces y usuario -->
    <div class="emp-links" style="width: fit-content; margin: 0 auto;">
  <a href="<?php echo BASE_URL; ?>index.php?controller=employee&action=addSaleForm">Registrar Venta</a>
  <a href="<?php echo BASE_URL; ?>index.php?controller=employee&action=listSalesEmployee">Mis Ventas</a>
  <a href="<?php echo BASE_URL; ?>index.php?controller=employee&action=listProductsEmployee">Productos</a>
  <a href="<?php echo BASE_URL; ?>index.php?controller=employee&action=profile">👤 Mi Perfil</a>
  
</div>
<div class="user">
<span class="emp-user">Hola, <strong><?php echo htmlspecialchars($username); ?></strong></span>
  <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=logout" class="emp-logout">Cerrar Sesión</a>
</div>
  </nav>
</header>
