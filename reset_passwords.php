<?php
// reset_passwords.php
// Script para resetear las contraseñas de todos los usuarios a '123456'

require_once __DIR__ . '/config/database.php';

// Conectar a la BD
$conn = Database::connect();

// Hash de la contraseña 123456
$newPassword = password_hash('123456', PASSWORD_BCRYPT);

// Actualizar todos los usuarios
$sql = "UPDATE users SET password = :password";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':password', $newPassword);

try {
    if ($stmt->execute()) {
        echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; font-weight: bold; text-align: center;'>";
        echo "✓ Contraseñas reseteadas exitosamente a '123456'<br>";
        echo "Usuarios disponibles:<br>";
        echo "• admin / 123456<br>";
        echo "• empleado1 / 123456<br>";
        echo "• Ale / 123456<br>";
        echo "<a href='index.php?controller=auth&action=login' style='color: #155724; text-decoration: underline;'>Ir al login</a>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; font-weight: bold;'>";
        echo "✗ Error al actualizar las contraseñas";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; font-weight: bold;'>";
    echo "✗ Error: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>
