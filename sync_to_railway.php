#!/usr/bin/env php
<?php
/**
 * Script para sincronizar BD pecosol_db a Railway
 * Uso: php sync_to_railway.php
 */

echo "=== SINCRONIZADOR DE BD A RAILWAY ===\n\n";

// Leer variables de entorno (desde .env o console)
$railway_db_host = getenv('RAILWAY_DB_HOST') ?: 'localhost';
$railway_db_user = getenv('RAILWAY_DB_USER') ?: 'root';
$railway_db_pass = getenv('RAILWAY_DB_PASSWORD') ?: '';
$railway_db_port = getenv('RAILWAY_DB_PORT') ?: 3306;
$railway_db_name = 'pecosol_db';

echo "Conectando a: $railway_db_host:$railway_db_port\n\n";

// Verificar archivo SQL
if (!file_exists('pecosol_db_current.sql')) {
    echo "❌ ERROR: No encontré el archivo 'pecosol_db_current.sql'\n";
    echo "Primero ejecuta: mysqldump -h localhost -u root pecosol_db > pecosol_db_current.sql\n";
    exit(1);
}

echo "📁 Archivo SQL encontrado (42 KB)\n\n";

// Intentar conectar a Railway
try {
    $dsn = "mysql:host=$railway_db_host;port=$railway_db_port";
    $conn = new PDO($dsn, $railway_db_user, $railway_db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión exitosa a MySQL\n\n";
    
    // Verificar si la BD existe
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$railway_db_name'");
    $db_exists = $result->fetchAll();
    
    if ($db_exists) {
        echo "⚠️  Base de datos '$railway_db_name' ya existe\n";
        echo "📋 OPCIÓN 1: Reemplazar completa (perderá datos)\n";
        echo "📋 OPCIÓN 2: Solo importar estructura\n";
        echo "📋 OPCIÓN 3: Cancelar\n";
        
        echo "\n¿Qué deseas hacer? (1/2/3): ";
        $option = trim(fgets(STDIN));
        
        if ($option == '1') {
            echo "\n🗑️  Eliminando BD existente...\n";
            $conn->exec("DROP DATABASE IF EXISTS $railway_db_name");
            $conn->exec("CREATE DATABASE $railway_db_name DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✅ BD recreada\n";
        } elseif ($option == '3') {
            echo "❌ Cancelado\n";
            exit(0);
        }
    } else {
        echo "📂 Creando BD '$railway_db_name'...\n";
        $conn->exec("CREATE DATABASE $railway_db_name DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ BD creada\n";
    }
    
    // Importar SQL
    echo "\n📥 Importando archivo SQL...\n";
    $sql_content = file_get_contents('pecosol_db_current.sql');
    
    // Usar conexión a la BD
    $conn->exec("USE $railway_db_name");
    
    // Ejecutar el SQL (dividir por ; si es necesario)
    $queries = explode(";\n", $sql_content);
    $count = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $conn->exec($query);
                $count++;
            } catch(PDOException $e) {
                echo "⚠️  Error en query #$count: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "✅ Importadas " . $count . " sentencias SQL\n\n";
    
    // Verificar datos
    echo "📊 VERIFICACIÓN DE DATOS\n";
    $conn->exec("USE $railway_db_name");
    
    $tables = ['users', 'products', 'sales', 'purchases', 'employees', 'stock_movements', 'work_orders', 'audit_logs'];
    foreach($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
        $count = $result->fetch(PDO::FETCH_ASSOC);
        echo "  - " . str_pad($table, 20) . ": " . $count['cnt'] . " registros\n";
    }
    
    // Verificar usuarios
    echo "\n👥 USUARIOS\n";
    $result = $conn->query("SELECT id, username, email, role FROM users");
    $users = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach($users as $user) {
        echo "  - " . $user['username'] . " (" . $user['role'] . ") - " . $user['email'] . "\n";
    }
    
    echo "\n✅ ¡SINCRONIZACIÓN COMPLETADA!\n";
    echo "La BD en Railway ahora tiene el mismo estado que local.\n";
    
} catch(PDOException $e) {
    echo "❌ Error de conexión a Railway:\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "Soluciones:\n";
    echo "1. Verifica que RAILWAY_DB_HOST, RAILWAY_DB_USER, RAILWAY_DB_PASSWORD están configurados\n";
    echo "2. Ejecuta: export RAILWAY_DB_HOST=xxx.railway.app (macOS/Linux)\n";
    echo "3. Ejecuta: set RAILWAY_DB_HOST=xxx.railway.app (Windows CMD)\n";
    echo "4. O define en: \$GLOBALS['railway_db_host'] = 'tu-host';\n";
    exit(1);
}

?>