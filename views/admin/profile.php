<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mi Perfil | Administrador Pecosol</title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" type="image/png" />
</head>
<body>

<?php
require_once __DIR__ . '/partials/header.php';

// Definir estas variables antes de cargarlas
$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
?>

<style>
  body {
    background-color: #1a1a2e;
    background-image: url('<?php echo BASE_URL; ?>assets/img/overlapping-circles.svg');
    background-repeat: repeat;
    background-size: 60px;
    background-attachment: fixed;
    color: #eaeaea;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
  }

  .profile-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: rgba(22, 33, 62, 0.95);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 255, 240, 0.15);
    border: 1px solid rgba(0, 255, 240, 0.2);
  }

  .profile-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #00fff0;
  }

  .profile-header h2 {
    color: #00fff0;
    margin-bottom: 10px;
    font-size: 2rem;
    text-shadow: 0 0 10px rgba(0, 255, 240, 0.5);
  }

  .profile-header p {
    color: #a0a0a0;
    font-size: 14px;
  }

  .profile-photo-section {
    text-align: center;
    margin-bottom: 40px;
  }

  .profile-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 48px;
    font-weight: bold;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }

  .profile-photo-hint {
    color: #999;
    font-size: 14px;
  }

  .form-section {
    margin-bottom: 30px;
  }

  .form-section h3 {
    color: #00fff0;
    margin-bottom: 20px;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .form-section h3::before {
    content: '';
    width: 4px;
    height: 24px;
    background: linear-gradient(180deg, #00fff0 0%, #00b8d4 100%);
    border-radius: 2px;
    box-shadow: 0 0 10px rgba(0, 255, 240, 0.5);
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group.full-width {
    grid-column: 1 / -1;
  }

  .form-group label {
    margin-bottom: 8px;
    color: #00fff0;
    font-weight: 500;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .form-group input,
  .form-group select {
    padding: 12px 16px;
    border: 2px solid rgba(0, 255, 240, 0.3);
    border-radius: 8px;
    font-size: 14px;
    background: rgba(26, 26, 46, 0.8);
    color: #eaeaea;
    transition: all 0.3s ease;
  }

  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: #00fff0;
    box-shadow: 0 0 15px rgba(0, 255, 240, 0.3);
    background: rgba(26, 26, 46, 0.95);
  }

  .form-group input[readonly] {
    background-color: rgba(160, 160, 160, 0.1);
    color: #a0a0a0;
    cursor: not-allowed;
    border-color: rgba(160, 160, 160, 0.3);
  }

  .form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(0, 255, 240, 0.2);
  }

  .btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-primary {
    background: linear-gradient(135deg, #00fff0 0%, #00b8d4 100%);
    color: #16213e;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 255, 240, 0.4);
  }

  .btn-secondary {
    background: rgba(160, 160, 160, 0.2);
    color: #eaeaea;
    border: 1px solid rgba(160, 160, 160, 0.3);
  }

  .btn-secondary:hover {
    background: rgba(160, 160, 160, 0.3);
    border-color: rgba(160, 160, 160, 0.5);
  }

  .alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid;
  }

  .alert-success {
    background: rgba(40, 167, 69, 0.2);
    border-color: rgba(40, 167, 69, 0.5);
    color: #4ade80;
  }

  .alert-error {
    background: rgba(220, 38, 38, 0.2);
    border-color: rgba(220, 38, 38, 0.5);
    color: #ff6b6b;
  }

  @media (max-width: 768px) {
    .profile-container {
      margin: 20px;
      padding: 20px;
    }

    .form-grid {
      grid-template-columns: 1fr;
    }

    .form-actions {
      flex-direction: column;
    }

    .btn {
      width: 100%;
    }
  }
</style>

<div class="profile-container">
  <div class="profile-header">
    <h2>Perfil de Usuario</h2>
    <p>Administra la información de tu perfil</p>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <span>✓</span> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
      <span>✗</span> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="profile-photo-section">
    <div class="profile-photo">
      <?php echo strtoupper(substr($user->full_name, 0, 1)); ?>
    </div>
    <p class="profile-photo-hint">Haz clic en la imagen para cambiar tu foto de perfil</p>
  </div>

  <form method="POST" action="index.php?controller=admin&action=updateProfile">
    
    <div class="form-section">
      <h3>Información Personal</h3>
      <div class="form-grid">
        <div class="form-group">
          <label for="username">Nombre de Usuario</label>
          <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user->username); ?>" required>
        </div>

        <div class="form-group">
          <label for="full_name">Nombre Completo</label>
          <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user->full_name); ?>" required>
        </div>

        <div class="form-group full-width">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
        </div>

        <div class="form-group">
          <label for="role">Rol</label>
          <input type="text" id="role" name="role" value="<?php echo ucfirst($user->role); ?>" readonly>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3>Cambiar Contraseña</h3>
      <p style="color: #a0a0a0; font-size: 14px; margin-bottom: 15px;">Deja los campos vacíos si no deseas cambiar tu contraseña</p>
      <div class="form-grid">
        <div class="form-group full-width">
          <label for="current_password">Contraseña Actual</label>
          <input type="password" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual">
        </div>

        <div class="form-group">
          <label for="new_password">Nueva Contraseña</label>
          <input type="password" id="new_password" name="new_password" placeholder="Mínimo 6 caracteres">
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirmar Nueva Contraseña</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la nueva contraseña">
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a href="index.php?controller=dashboard&action=adminHome" class="btn btn-secondary">Cancelar</a>
      <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
  </form>
</div>

<!-- Chatbot Widget -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
<script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
