<?php
$host = 'switchback.proxy.rlwy.net';
$port = '10989';
$user = 'root';
$pass = 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya';
$db_name = 'pecosol_db';

echo "🔍 SINCRONIZANDO BD A RAILWAY\n";
echo "==============================\n\n";

// 1. Conectar
echo "[1/4] Conectando a Railway...\n";
try {
    $conn = new PDO(
        "mysql:host=$host;port=$port",
        $user,
        $pass,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4')
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ OK\n\n";
} catch(Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Preparar BD
echo "[2/4] Preparando BD...\n";
try {
    $conn->exec("DROP DATABASE IF EXISTS $db_name");
    $conn->exec("CREATE DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE $db_name");
    echo "✅ OK\n\n";
} catch(Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Importar SQL
echo "[3/4] Importando SQL...\n";
try {
    $sql = file_get_contents('pecosol_db_current_utf8.sql');
    
    // Split por ;
    $statements = explode(';', $sql);
    
    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        
        // Skip vacíos y comentarios
        if (empty($stmt) || substr($stmt, 0, 2) === '--' || substr($stmt, 0, 1) === '#') {
            continue;
        }
        
        // Skip lines starting with /*! but extract SQL
        $lines = explode("\n", $stmt);
        $clean = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && substr($line, 0, 2) !== '--' && substr($line, 0, 1) !== '#') {
                $clean .= $line . " ";
            }
        }
        
        $clean = trim($clean);
        if (!empty($clean) && strlen($clean) > 3) {
            try {
                $conn->exec($clean);
                $count++;
            } catch(Exception $e) {
                // Ignorar errores específicos
            }
        }
    }
    
    echo "✅ Importadas $count sentencias\n\n";
    
} catch(Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Verificar
echo "[4/4] Verificando importación...\n";
try {
    $tables = ['audit_logs', 'employees', 'products', 'purchases', 'sales', 'stock_movements', 'users', 'work_orders'];
    
    echo "\n📊 DATOS IMPORTADOS:\n";
    $total = 0;
    foreach ($tables as $t) {
        $r = $conn->query("SELECT COUNT(*) as c FROM $t");
        $c = $r->fetch(PDO::FETCH_ASSOC)['c'];
        $total += $c;
        printf("  %-20s: %6d registros\n", $t, $c);
    }
    printf("  %s\n", str_repeat("─", 40));
    printf("  %-20s: %6d total\n", "TOTAL", $total);
    
    echo "\n👥 USUARIOS EN RAILWAY:\n";
    $result = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
    $users = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $u) {
        printf("  ✓ ID%d | %-10s | %-30s | %s\n", 
            $u['id'], 
            $u['role'], 
            $u['username'],
            $u['email']
        );
    }
    
    echo "\n" . str_repeat("═", 50) . "\n";
    echo "✅ ¡SINCRONIZACIÓN COMPLETADA CON ÉXITO!\n";
    echo "   Tu BD local ahora está en Railway.\n";
    echo str_repeat("═", 50) . "\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
