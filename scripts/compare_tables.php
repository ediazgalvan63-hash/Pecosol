<?php
$local = new PDO('mysql:host=127.0.0.1;dbname=pecosol_db;charset=utf8mb4', 'root', '');
$remote = new PDO('mysql:host=switchback.proxy.rlwy.net;port=10989;dbname=railway;charset=utf8mb4', 'root', 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya');
$lt = $local->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$rt = $remote->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo 'LOCAL: ' . implode(', ', $lt) . PHP_EOL;
echo 'RAILWAY: ' . implode(', ', $rt) . PHP_EOL;
echo 'ONLY LOCAL: ' . implode(', ', array_diff($lt, $rt)) . PHP_EOL;
echo 'ONLY RAILWAY: ' . implode(', ', array_diff($rt, $lt)) . PHP_EOL;
