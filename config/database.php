<?php
// config/database.php

class Database {
    // Cambia estos datos si tu usuario/contraseña de MySQL difieren
    private static $host     = "localhost";
    private static $db_name  = "pecosol_db";
    private static $username = "root";
    private static $password = "";   // Si tu XAMPP tiene contraseña, colócala aquí
    private static $port     = 3306; // Puerto por defecto MySQL; ajusta a 3307 si tu instancia XAMPP usa ese puerto
    public static  $conn;

    public static function connect() {
        self::$conn = null;
        try {
            // Allow overriding credentials with environment variables (useful in XAMPP or CI)
            $host = getenv('DB_HOST') ?: self::$host;
            $db   = getenv('DB_DATABASE') ?: self::$db_name;
            $user = getenv('DB_USERNAME') ?: self::$username;
            $pass = getenv('DB_PASSWORD') ?: self::$password;
            $port = getenv('DB_PORT') ?: self::$port;

            self::$conn = new PDO(
                "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $db,
                $user,
                $pass,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
            );
            // Para que lance excepciones en caso de error
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Muestra error si no se conecta (solo para desarrollo)
            echo "Error de conexión a la base de datos: " . $exception->getMessage() . "\n";
            echo "Comprueba las credenciales en 'config/database.php' o configura las variables de entorno DB_USERNAME/DB_PASSWORD.\n";
            exit;
        }
        return self::$conn;
    }
}


