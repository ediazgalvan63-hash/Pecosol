<?php
// debug_password.php
// Script para debuggear el problema de verificación de contraseña

require_once __DIR__ . '/config/database.php';

$conn = Database::connect();

// Obtener el usuario admin
$sql = "SELECT * FROM users WHERE username = 'admin' LIMIT 1";
$stmt = $conn->query($sql);
$user = $stmt->fetch(PDO::FETCH_OBJ);

echo "<pre>";
echo "=== DEBUG CONTRASEÑA ===\n\n";

if ($user) {
    echo "Usuario encontrado: " . $user->username . "\n";
    echo "Hash almacenado: " . $user->password . "\n\n";
    
    // Crear un nuevo hash de 123456
    $testPassword = '123456';
    $newHash = password_hash($testPassword, PASSWORD_BCRYPT);
    echo "Nuevo hash de 123456: " . $newHash . "\n\n";
    
    // Probar verificación
    echo "Verificando con password_verify():\n";
    $result1 = password_verify($testPassword, $user->password);
    echo "password_verify('123456', hash_almacenado) = " . var_export($result1, true) . "\n\n";
    
    // Información del hash
    $info = password_get_info($user->password);
    echo "Info del hash almacenado:\n";
    echo "Algoritmo: " . $info['algo'] . "\n";
    echo "Opciones: " . print_r($info['options'], true) . "\n";
} else {
    echo "Usuario admin no encontrado\n";
}

echo "</pre>";
?>
