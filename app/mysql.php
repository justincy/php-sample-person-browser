#!/usr/bin/env php
<?PHP

if(file_exists("/home/dotcloud/environment.json")) {
    /* configuration on dotCloud */
    require('env.php');
    
    $dsn = "mysql:host=$host;port=$port";
}
else {
    /* your local configuration */
    $dsn = 'mysql:dbname=test;host=127.0.0.1;';
    $user = 'root';
    $password = 'root';
    $dbname = 'test-php';
}

echo "Connection to the database..";
$tries = 0;
connection:
try {
    echo ".";
    flush();
    $dbh = new PDO("mysql:host=$host;port=$port", $user, $password);
    echo "\n";
    echo "Create DB '$dbname' if needed\n";
    $dbh->exec("CREATE DATABASE IF NOT EXISTS `$dbname`") or die(print_r($dbh->errorInfo(), true));
} catch (Exception $e) {
    sleep(2);
    if(++$tries <= 120)
        goto connection;
    else{
        echo "\nCould not connect to the database server\n";
        exit(1);
    }        
}
