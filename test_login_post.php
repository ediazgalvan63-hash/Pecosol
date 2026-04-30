<?php
// test_login_post.php
// Script para testear POST de login directamente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iniciar sesión
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/User.php';

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    echo "<pre>";
    echo "=== PRUEBA POST LOGIN ===\n";
    echo "Username: '$username' (longitud: " . strlen($username) . ")\n";
    echo "Password: '$password' (longitud: " . strlen($password) . ")\n\n";
    
    if ($username === '' || $password === '') {
        echo "✗ Error: Usuario o contraseña vacíos\n";
    } else {
        $userModel = new User();
        $user = $userModel->findByUsername($username);
        
        if ($user) {
            echo "✓ Usuario encontrado: " . $user->username . "\n";
            $verifyResult = $userModel->verifyPassword($password, $user->password);
            echo "Verificación contraseña: " . var_export($verifyResult, true) . "\n";
            
            if ($verifyResult) {
                echo "✓ Contraseña CORRECTA!\n";
                $_SESSION['user_id']   = $user->id;
                $_SESSION['username']  = $user->username;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['role']      = $user->role;
                
                echo "Sesión establecida, redirigiendo...\n";
                if ($user->role === 'admin') {
                    echo "Destino: index.php?controller=dashboard&action=adminHome\n";
                    // header('Location: index.php?controller=dashboard&action=adminHome');
                } else {
                    echo "Destino: index.php?controller=dashboard&action=employeeHome\n";
                    // header('Location: index.php?controller=dashboard&action=employeeHome');
                }
            } else {
                echo "✗ Contraseña INCORRECTA\n";
            }
        } else {
            echo "✗ Usuario no encontrado\n";
        }
    }
    echo "</pre>";
} else {
    // Mostrar formulario de prueba
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Test Login POST</title>
        <style>
            body { font-family: Arial; margin: 20px; }
            form { display: flex; flex-direction: column; max-width: 300px; gap: 10px; }
            input { padding: 8px; }
            button { padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h2>Test Login POST</h2>
        <form method="POST">
            <label>Usuario:
                <input type="text" name="username" value="admin" required autofocus>
            </label>
            <label>Contraseña:
                <input type="password" name="password" value="123456" required>
            </label>
            <button type="submit">Enviar</button>
        </form>
    </body>
    </html>
    <?php
}
?>
