<?php
require 'config/database.php';
$db = Database::connect();
$today = $db->query('SELECT CURDATE() as d, NOW() as n')->fetch(PDO::FETCH_ASSOC);
echo "CURDATE=" . $today['d'] . " NOW=" . $today['n'] . "\n";
$result = $db->query('SELECT id, user_id, product_id, total_price, sale_date, description FROM sales ORDER BY sale_date DESC LIMIT 20');
foreach ($result as $row) {
    echo implode(' | ', [$row['id'], $row['user_id'], $row['product_id'], $row['total_price'], $row['sale_date'], $row['description']]) . "\n";
}
