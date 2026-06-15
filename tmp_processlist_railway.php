<?php
$dsn = 'mysql:host=switchback.proxy.rlwy.net;port=10989';
$user = 'root';
$pass = 'LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya';
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$stmt = $pdo->query("SELECT ID, USER, HOST, DB, COMMAND, TIME, STATE, INFO FROM information_schema.processlist WHERE DB='railway';");
foreach ($stmt as $row) {
    echo implode(' | ', [$row['ID'], $row['USER'], $row['HOST'], $row['DB'], $row['COMMAND'], $row['TIME'], $row['STATE'], $row['INFO']]) . "\n";
}
?>
