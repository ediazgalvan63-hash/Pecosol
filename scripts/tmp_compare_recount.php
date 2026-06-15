<?php
$local = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pecosol_db;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$remote = new PDO('mysql:host=switchback.proxy.rlwy.net;port=10989;dbname=railway;charset=utf8mb4','root','LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

function out($text) { echo $text . "\n"; }

$out = [];
$queries = [
    'local_products' => 'SELECT COUNT(*) FROM products',
    'remote_products' => 'SELECT COUNT(*) FROM products',
    'local_stock' => 'SELECT COALESCE(SUM(stock),0) FROM products',
    'remote_stock' => 'SELECT COALESCE(SUM(stock),0) FROM products',
    'local_reconteos' => "SELECT COUNT(*) FROM stock_movements WHERE notes LIKE '%reconteo%'",
    'remote_reconteos' => "SELECT COUNT(*) FROM stock_movements WHERE notes LIKE '%reconteo%'",
];

foreach ($queries as $label => $sql) {
    $pdo = strpos($label, 'local_') === 0 ? $local : $remote;
    $value = $pdo->query($sql)->fetchColumn();
    $out[$label] = $value;
}

out('local_products=' . $out['local_products']);
out('remote_products=' . $out['remote_products']);
out('local_stock=' . $out['local_stock']);
out('remote_stock=' . $out['remote_stock']);
out('local_reconteos=' . $out['local_reconteos']);
out('remote_reconteos=' . $out['remote_reconteos']);

$outFile = __DIR__ . '/tmp_compare_recount_result.txt';
file_put_contents($outFile, implode("\n", [
    'local_products=' . $out['local_products'],
    'remote_products=' . $out['remote_products'],
    'local_stock=' . $out['local_stock'],
    'remote_stock=' . $out['remote_stock'],
    'local_reconteos=' . $out['local_reconteos'],
    'remote_reconteos=' . $out['remote_reconteos'],
]) . "\n");
out('WROTE ' . $outFile);
