<?php
// bypass_auth.php
// Script que reemplaza temporalmente el AuthController para permitir login sin contraseña
// Uso: Incluir este archivo antes del AuthController o acceder directamente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/User.php';

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    error_log("=== BYPASS AUTH LOGIN ATTEMPT ===");
    error_log("Username: '" . $username . "'");

    $error = '';
    if ($username === '') {
        $error = 'Usuario es obligatorio.';
        require __DIR__ . '/views/auth/login.php';
        exit;
    }

    // Buscar usuario sin verificar contraseña
    $userModel = new User();
    $user = $userModel->findByUsername($username);
    
    if ($user) {
        error_log("User found: " . $user->username);
        
        // Establecer sesión sin verificar contraseña
        $_SESSION['user_id']   = $user->id;
        $_SESSION['username']  = $user->username;
        $_SESSION['full_name'] = $user->full_name;
        $_SESSION['role']      = $user->role;
        
        error_log("Login successful without password verification!");
        
        // Redirigir según rol
        if ($user->role === 'admin') {
            header('Location: index.php?controller=dashboard&action=adminHome');
            exit;
        } else {
            header('Location: index.php?controller=dashboard&action=employeeHome');
            exit;
        }
    } else {
        $error = 'Usuario no existe.';
        error_log("User not found");
        require __DIR__ . '/views/auth/login.php';
        exit;
    }
}

// Si es GET, mostrar formulario de login
require __DIR__ . '/views/auth/login.php';
?>
