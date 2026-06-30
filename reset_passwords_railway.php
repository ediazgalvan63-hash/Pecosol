<?php
// reset_passwords_railway.php
// Script para ejecutar en Railway y resetear contraseñas
// Acceso: https://tu-dominio.railway.app/reset_passwords_railway.php

// Protección: verifica que sea accedido desde Railway o localhost
$allowed_hosts = ['localhost', '127.0.0.1', $_SERVER['HTTP_HOST'] ?? ''];
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Reset Contraseñas - Pecosol</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 50px auto; }";
echo ".success { background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 20px; border-radius: 5px; margin: 20px 0; }";
echo "table { width: 100%; border-collapse: collapse; margin-top: 15px; }";
echo "td { padding: 10px; border: 1px solid #ddd; }";
echo "a { color: #007bff; text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

// Verificar conexión a BD
require_once __DIR__ . '/config/database.php';

try {
    $conn = Database::connect();
    
    // Hash de la contraseña 123456
    $newPassword = password_hash('123456', PASSWORD_BCRYPT);
    
    // Actualizar todos los usuarios
    $sql = "UPDATE users SET password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':password', $newPassword);
    
    if ($stmt->execute()) {
        echo "<div class='success'>";
        echo "<h2>✓ Éxito</h2>";
        echo "<p>Contraseñas reseteadas exitosamente a <strong>'123456'</strong></p>";
        echo "<p>Usuarios disponibles:</p>";
        echo "<table>";
        echo "<tr><td><strong>Usuario</strong></td><td><strong>Contraseña</strong></td><td><strong>Rol</strong></td></tr>";
        echo "<tr><td>admin</td><td>123456</td><td>Administrador</td></tr>";
        echo "<tr><td>empleado1</td><td>123456</td><td>Empleado</td></tr>";
        echo "<tr><td>Ale</td><td>123456</td><td>Empleado</td></tr>";
        echo "</table>";
        echo "<p style='margin-top: 20px;'>";
        echo "<a href='index.php?controller=auth&action=login'>← Ir al login</a>";
        echo "</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h2>✗ Error</h2>";
        echo "<p>No se pudieron actualizar las contraseñas.</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>✗ Error de conexión</h2>";
    echo "<p>Asegúrate de que las variables de entorno estén correctamente configuradas:</p>";
    echo "<ul>";
    echo "<li>DB_HOST</li>";
    echo "<li>DB_PORT</li>";
    echo "<li>DB_DATABASE</li>";
    echo "<li>DB_USERNAME</li>";
    echo "<li>DB_PASSWORD</li>";
    echo "</ul>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<strong>Nota:</strong> Este script es para resetear contraseñas. Si el error persiste, verifica que la BD esté correctamente configurada con variables de entorno.";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
