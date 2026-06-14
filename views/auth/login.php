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
  <!-- Inline fallback: full stylesheet in case external CSS is blocked -->
  <style>
/* assets/css/style.css */

/* Reset básico */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Fondo de la página */
body {
  background-color: #1a1a2e;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #eaeaea;
  background-image: url('../img/overlapping-circles.svg');
  background-repeat: repeat;
  background-size: 60px;
  background-attachment: fixed;
}

/* Contenedor centrado */
.container-login {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}


/* Tarjeta neón */
.neon-card {
  position: relative;
  background-color: #16213e;
  border-radius: 20px;
  padding: 2rem;
  width: 90%;            /* Ocupa el 90% del contenedor */
  max-width: 650px;      /* Hasta 500px de ancho */
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
}


/* Brillo difuminado externo */
.neon-card::before {
  content: '';
  position: absolute;
  top: -5px;
  left: -5px;
  right: -5px;
  bottom: -5px;
  border: 1px solid rgba(0, 255, 255, 0.5);
  border-radius: 14px;
  filter: blur(8px);
  z-index: -1;
}

/* Título neón */
.neon-card h2 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 1.5rem;
  color: #00fff0;
  text-shadow: 0 0 8px #00fff0;
}

/* Etiquetas de formulario */
.form-label {
  display: block;
  margin-bottom: 0.5rem;
  color: #a0a0a0;
}

/* Inputs transparentes con borde neón al focus */
.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  background: transparent;
  border: 2px solid #0f3460;
  border-radius: 8px;
  color: #eaeaea;
  font-size: 1rem;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.form-control::placeholder {
  color: #555;
}

.form-control:focus {
  outline: none;
  border-color: #00fff0;
  box-shadow: 0 0 8px #00fff0;
}
/* Button neón */
.btn-neon {
  --glow-color: #00fff0;
  --glow-spread-color: rgba(0, 255, 240, 0.4);
  --btn-color: #0f1113;

  display: block;
  margin: 1.5rem auto 0;
  padding: 0.9em 2.5em;
  max-width: 280px;

  color: var(--glow-color);
  background-color: var(--btn-color);
  border: 2px solid var(--glow-color);
  border-radius: 50px;
  font-size: 1rem;
  font-weight: bold;
  text-align: center;
  cursor: pointer;
  text-shadow: 0 0 8px var(--glow-color);
  box-shadow:
    0 0 8px var(--glow-color),
    0 0 20px var(--glow-spread-color),
    inset 0 0 10px rgba(0, 255, 240, 0.2);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn-neon::after {
  content: "";
  position: absolute;
  top: 120%;
  left: 0;
  height: 100%;
  width: 100%;
  background-color: var(--glow-spread-color);
  filter: blur(2em);
  opacity: 0.6;
  transform: perspective(1.5em) rotateX(35deg) scale(1, 0.6);
  pointer-events: none;
}

.btn-neon:hover {
  background-color: var(--glow-color);
  color: var(--btn-color);
  box-shadow:
    0 0 10px var(--glow-color),
    0 0 30px var(--glow-spread-color),
    inset 0 0 14px rgba(0, 255, 240, 0.4);
}

.btn-neon:active {
  box-shadow:
    0 0 6px var(--glow-color),
    0 0 16px var(--glow-spread-color),
    inset 0 0 10px rgba(0, 255, 240, 0.4);
  transform: scale(0.98);
}


/* Mensaje de error */
.error {
  text-align: center;
  color: #ff4d4d;
  margin-bottom: 1rem;
}

/* ─── Navbar oscuro ───────────────────────────────────────────────────────── */

/* 1) Fondo del navbar */
.navbar {
  background-color: #0f3460 !important;
}

/* 2) Texto de marca y enlaces */
.navbar .navbar-brand,
.navbar .nav-link {
  color: #eaeaea !important;
}

/* 3) Hover: acento turquesa */
.navbar .nav-link:hover {
  color: #00fff0 !important;
}

/* 4) Enlace “Cerrar Sesión” en rojo suave */
.navbar .cerrar-sesion {
  color: #ff6b6b !important;
}
.navbar .cerrar-sesion:hover {
  color: #ff8787 !important;
}

/* Boton agregar producto */
.button {
  position: relative;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: bold;
  color: #00fff0;
  background-color: #0f172a;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  text-decoration: none;
  z-index: 1;
  transition: color 0.3s ease, background-color 0.3s ease;
  overflow: hidden;
  box-shadow: 0 0 12px rgba(0, 255, 240, 0.3);
}

.button::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  width: calc(100% + 4px);
  height: calc(100% + 4px);
  background: linear-gradient(135deg, #00fff0, #5eead4);
  border-radius: 12px;
  z-index: -2;
  transition: transform 0.6s ease;
}

.button::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #0f172a;
  border-radius: 10px;
  z-index: -1;
}

.button:hover::before {
  transform: rotate(180deg);
}

.button:hover {
  color: #0f172a;
  background-color: #00fff0;
  box-shadow: 0 0 18px rgba(0, 255, 240, 0.6);
}

.button:active::before {
  transform: scale(0.9);
}

/* Botones globales unificados */
button[type="submit"],
button[type="reset"],
button[type="button"],
input[type="submit"],
input[type="reset"],
input[type="button"],
.btn,
.button,
a.button,
.btn-primary,
.btn-secondary,
.btn-ghost,
.btn-add-large,
.action-button,
.btn-edit,
.btn-delete,
.btn-submit {
  position: relative !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.5rem !important;
  padding: 14px 28px !important;
  min-height: 48px !important;
  font-size: 16px !important;
  font-weight: 800 !important;
  line-height: 1.2 !important;
  color: #00fff0 !important;
  background-color: #0f172a !important;
  border: 1px solid rgba(0, 255, 240, 0.4) !important;
  border-radius: 18px !important;
  cursor: pointer !important;
  text-decoration: none !important;
  text-align: center !important;
  white-space: nowrap !important;
  transition: all 0.25s ease !important;
  overflow: hidden !important;
  box-shadow: 0 0 22px rgba(0, 255, 240, 0.25) !important;
}

.btn::before,
.button::before,
a.button::before,
.btn-primary::before,
.btn-secondary::before,
.btn-ghost::before,
.btn-add-large::before,
.action-button::before,
.btn-edit::before,
.btn-delete::before,
.btn-submit::before {
  content: '' !important;
  position: absolute !important;
  top: -2px !important;
  left: -2px !important;
  width: calc(100% + 4px) !important;
  height: calc(100% + 4px) !important;
  background: linear-gradient(135deg, rgba(0,255,240,0.35), rgba(94,234,212,0.2)) !important;
  border-radius: 22px !important;
  z-index: -2 !important;
  transition: transform 0.35s ease !important;
}

.btn::after,
.button::after,
a.button::after,
.btn-primary::after,
.btn-secondary::after,
.btn-ghost::after,
.btn-add-large::after,
.action-button::after,
.btn-edit::after,
.btn-delete::after,
.btn-submit::after {
  content: '' !important;
  position: absolute !important;
  inset: 0 !important;
  background-color: inherit !important;
  border-radius: inherit !important;
  z-index: -1 !important;
}

.btn:hover,
.button:hover,
a.button:hover,
.btn-primary:hover,
.btn-secondary:hover,
.btn-ghost:hover,
.btn-add-large:hover,
.action-button:hover,
.btn-edit:hover,
.btn-delete:hover,
.btn-submit:hover,
button[type="submit"]:hover,
button[type="reset"]:hover,
button[type="button"]:hover,
input[type="submit"]:hover,
input[type="reset"]:hover,
input[type="button"]:hover {
  color: #0f172a !important;
  background-color: #00fff0 !important;
  border-color: rgba(0, 255, 240, 0.55) !important;
  box-shadow: 0 0 28px rgba(0, 255, 240, 0.55) !important;
}

.btn:active,
.button:active,
a.button:active,
.btn-primary:active,
.btn-secondary:active,
.btn-ghost:active,
.btn-add-large:active,
.action-button:active,
.btn-edit:active,
.btn-delete:active,
.btn-submit:active,
button[type="submit"]:active,
button[type="reset"]:active,
button[type="button"]:active,
input[type="submit"]:active,
input[type="reset"]:active,
input[type="button"]:active {
  transform: translateY(1px) !important;
}

.btn-primary,
.button-primary {
  background: linear-gradient(90deg, #00fff0, #00e6d6) !important;
  color: #012 !important;
  border: none !important;
  box-shadow: 0 0 20px rgba(0, 255, 240, 0.35) !important;
}

.form-actions .btn-primary {
  background: linear-gradient(90deg, #00b9d8, #0074d7) !important;
  color: #f8fbff !important;
  border: 1px solid rgba(0, 255, 240, 0.6) !important;
  box-shadow: 0 0 28px rgba(0, 255, 240, 0.45) !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.28) !important;
}

.form-actions .btn-primary:hover {
  background: linear-gradient(90deg, #00d4f0, #00a2ff) !important;
  color: #012 !important;
}

.form-actions .btn-ghost {
  background: rgba(0, 255, 240, 0.08) !important;
  color: #dffeff !important;
  border-color: rgba(0, 255, 240, 0.55) !important;
  box-shadow: 0 0 18px rgba(0, 255, 240, 0.18) !important;
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
