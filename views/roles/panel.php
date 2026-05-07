<?php
// views/roles/panel.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$rolePanel = $rolePanel ?? null;
if (!$rolePanel) {
  header('Location: ' . BASE_URL . 'index.php?controller=dashboard&action=home');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($rolePanel['title']); ?> | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="icon" href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" type="image/png" />
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
      max-width: 1100px;
      margin: 50px auto;
      padding: 0 20px;
    }

    h1 {
      margin: 0 0 8px;
      color: #00fff0;
      text-align: center;
    }

    .subtitle {
      text-align: center;
      color: #a0cfe8;
      margin-bottom: 26px;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 18px;
    }

    .card {
      display: block;
      background: rgba(15, 52, 96, 0.92);
      border: 1px solid rgba(0,255,240,0.18);
      border-radius: 16px;
      padding: 18px;
      text-decoration: none;
      color: #eaeaea;
      box-shadow: 0 0 18px rgba(0,255,240,0.08);
      transition: transform .15s ease, box-shadow .15s ease;
    }

    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 14px 28px rgba(0,0,0,0.35);
    }

    .card h3 {
      margin: 0 0 8px;
      color: #00fff0;
      font-size: 1.1rem;
    }

    .card p {
      margin: 0;
      color: #cfefff;
      line-height: 1.35;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../employee/partials/header.php'; ?>
  <div class="container">
    <h1><?php echo htmlspecialchars($rolePanel['title']); ?></h1>
    <div class="subtitle"><?php echo htmlspecialchars($rolePanel['subtitle'] ?? ''); ?></div>
    <div class="cards">
      <?php foreach (($rolePanel['cards'] ?? []) as $card): ?>
        <a class="card" href="<?php echo htmlspecialchars($card['href']); ?>">
          <h3><?php echo htmlspecialchars($card['title']); ?></h3>
          <p><?php echo htmlspecialchars($card['desc']); ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>

