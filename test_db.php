<?php
// test_db.php

// Incluimos el autoload manual para cargar Database:
require_once __DIR__ . '/config/database.php';

echo "<h3>Prueba de conexión a la base de datos</h3>";

try {
    $db = Database::connect();
    if ($db) {
        echo "<p style='color: green;'>Conexión exitosa a pecosol_db.</p>";
    } else {
        echo "<p style='color: red;'>No se pudo conectar a pecosol_db.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Excepción capturada: " . $e->getMessage() . "</p>";
}
