<?php
// views/auth/login.php
// NOTA: Antes de usar $error, comprobamos si está definida
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inicio de Sesión | Pecosol</title>
  <base href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- Favicon -->
  <link rel="icon" href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>assets/img/LogoPecosol.png" type="image/png">
  <!-- CSS neón principal -->
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>assets/css/style.css">

  <!-- Ajustes puntuales: espaciados e íconos -->
  <style>
    body {
      background-color: #1a1a2e;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #eaeaea;
      min-height: 100vh;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container-login {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      min-height: 100vh;
      padding: 1rem;
      background: radial-gradient(circle at top, rgba(0, 255, 240, 0.12), transparent 32%),
        radial-gradient(circle at left, rgba(255, 255, 255, 0.03), transparent 28%),
        #1a1a2e;
    }

    .neon-card {
      width: min(100%, 460px);
      background-color: rgba(22, 33, 62, 0.96);
      border-radius: 22px;
      padding: 2rem 2rem 2.5rem;
      box-shadow: 0 0 30px rgba(0, 255, 255, 0.18);
      border: 1px solid rgba(0, 255, 240, 0.18);
    }

    .form-control {
      width: 100%;
      padding: 0.85rem 1rem;
      background: rgba(15, 52, 96, 0.14);
      border: 1.5px solid rgba(15, 52, 96, 0.9);
      border-radius: 10px;
      color: #eaeaea;
      font-size: 1rem;
      transition: border-color 0.3s, box-shadow 0.3s, background-color 0.3s;
    }

    .form-control::placeholder {
      color: #7b7b9e;
    }

    .form-control:focus {
      outline: none;
      border-color: #00fff0;
      box-shadow: 0 0 10px rgba(0, 255, 240, 0.25);
      background-color: rgba(15, 52, 96, 0.24);
    }

    .btn-neon {
      display: block;
      width: 100%;
      margin: 1.75rem auto 0;
      padding: 0.9rem 1.1rem;
      color: #00fff0;
      background-color: #0f1113;
      border: 2px solid #00fff0;
      border-radius: 50px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      text-shadow: 0 0 8px #00fff0;
      box-shadow: 0 0 12px rgba(0, 255, 240, 0.18);
      transition: all 0.25s ease;
    }

    .btn-neon:hover {
      background-color: #00fff0;
      color: #0f1113;
      box-shadow: 0 0 16px rgba(0, 255, 240, 0.4);
    }

    .error {
      text-align: center;
      color: #ff6b6b;
      margin-bottom: 1rem;
      font-weight: 600;
    }

    /* Centrado y separación logo → título */
    .logo-wrapper {
      text-align: center;
      margin-bottom: 0.25rem;
    }

    /* Icono en modo “normal” (sin glow ni drop-shadow) */
    .logo-img {
      max-width: 120px;
      display: block;
      margin: 0 auto;
      filter: none !important;
      /* Si quieres, puedes ajustar aquí un poco de sombra suave: 
      box-shadow: 0 0 4px rgba(0,0,0,0.2); */
    }

    /* Título con glow (sigue neón) */
    .logo-wrapper h2 {
      font-size: 2rem;
      color: #00fff0;
      text-shadow: 0 0 8px #00fff0;
      margin: 0.25rem 0 1rem;
    }
    .logo-wrapper h3 {
      font-size: 2rem;
      color: #00fff0;
      text-shadow: 0 0 5px #00fff0;
      margin: 0.25rem 0 1rem;
    }

    /* Más espacio tras cada label */
    .form-label {
      display: block;
      margin-bottom: 0.75rem;
      color: #a0a0a0;
    }

    /* Empuja la sección de contraseña un poco hacia abajo */
    .mb-4 {
      margin-top: 1.5rem !important;
    }

    /* Baja el botón para dejar más espacio */
    .btn-neon {
      margin-top: 2.5rem !important;
    }

    /* Íconos junto a labels, tintados en turquesa */
    .input-icon {
      width: 1.2rem;
      vertical-align: middle;
      margin-right: 0.4rem;
      filter: brightness(0) saturate(100%) invert(61%) sepia(75%) saturate(300%) hue-rotate(141deg);
    }
  </style>
</head>
<body>
  <div class="container-login">
    <div class="neon-card">

      <!-- Logo principal (sin glow) y título neón -->
      <div class="logo-wrapper">
        <img
          src="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png"
          alt="Logo Pecosol"
          class="logo-img"
        />
        <h3>PECOSOL</h3>
        <h2>Iniciar Sesión</h2>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>index.php?controller=auth&action=login" method="post">
        <div class="mb-3">
          <label for="username" class="form-label">
            <img src="<?php echo BASE_URL; ?>assets/img/users.png" alt="" class="input-icon" />
            Usuario:
          </label>
          <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            placeholder="Ingresa tu usuario"
            required
            autofocus
          />
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">
            <img src="<?php echo BASE_URL; ?>assets/img/password.png" alt="" class="input-icon" />
            Contraseña:
          </label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Contraseña"
            required
          />
        </div>

        <button type="submit" class="btn-neon">Iniciar sesión</button>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
