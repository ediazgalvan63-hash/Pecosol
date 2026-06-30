<?php
// debug_login.php
// Script para simular exactamente el proceso de login del AuthController

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

echo "<pre>";
echo "=== DEBUG LOGIN PROCESS ===\n\n";

// Simular los datos del formulario
$username = 'admin';
$password = '123456';

echo "Datos ingresados:\n";
echo "Username: '$username'\n";
echo "Password: '$password'\n";
echo "Longitud password: " . strlen($password) . "\n\n";

// Crear modelo de usuario
$userModel = new User();

// Buscar usuario
echo "Buscando usuario...\n";
$user = $userModel->findByUsername($username);

if ($user) {
    echo "✓ Usuario encontrado\n";
    echo "  ID: " . $user->id . "\n";
    echo "  Username: " . $user->username . "\n";
    echo "  Full Name: " . $user->full_name . "\n";
    echo "  Role: " . $user->role . "\n";
    echo "  Hash almacenado: " . $user->password . "\n\n";
    
    // Verificar contraseña
    echo "Verificando contraseña...\n";
    $verifyResult = $userModel->verifyPassword($password, $user->password);
    echo "userModel->verifyPassword() = " . var_export($verifyResult, true) . "\n";
    
    // Debug de password_verify también
    echo "\nDebug adicional:\n";
    echo "password_verify('" . $password . "', hash) = " . var_export(password_verify($password, $user->password), true) . "\n";
} else {
    echo "✗ Usuario no encontrado\n";
}

echo "</pre>";
?>
