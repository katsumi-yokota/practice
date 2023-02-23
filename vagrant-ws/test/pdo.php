<?php
$config = require_once 'config.php';
$dbUser = $config['db_user'];
$dbPass = $config['db_pass'];
$dbHost = $config['db_host'];
$dsn = "mysql:dbname=form-db;host=$dbHost;charset=utf8";
$pdo = new PDO($dsn, $dbUser, $dbPass);
?>
