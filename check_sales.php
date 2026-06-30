<?php
require 'config/database.php';
$db = Database::connect();
$result = $db->query('SELECT COUNT(*) as total FROM sales');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo 'Total ventas en BD: ' . $row['total'] . PHP_EOL;
$result = $db->query('SELECT COUNT(*) as today FROM sales WHERE DATE(sale_date) = CURDATE()');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo 'Ventas hoy en BD: ' . $row['today'] . PHP_EOL;
?>