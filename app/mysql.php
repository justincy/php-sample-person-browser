#!/usr/bin/env php
<?PHP

require('env.php');

$dbh = new PDO("mysql:host=$host;port=$port", $user, $password);

echo "Create DB '$dbname' if needed\n";
$dbh->exec("CREATE DATABASE IF NOT EXISTS $dbname;") or die(print_r($dbh->errorInfo(), true));