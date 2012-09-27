<?php

/* Read the environment.json file */
if(file_exists("/home/dotcloud/environment.json")) {
    $env =  json_decode(file_get_contents("/home/dotcloud/environment.json"));
    $dsn = 'mysql:dbname:test;host=' .  $env['DOTCLOUD_DB_MYSQL_HOST'] . ';port=' . $env['DOTCLOUD_DB_MYSQL_PORT'];
    $user = 'root';
    $password =  $env['DOTCLOUD_DB_MYSQL_PASSWORD'];  
}
else {
    $dsn = 'mysql:dbname=test;host=127.0.0.1;';
    $user = 'root';
    $password = 'root';
}

/* Create a PDO instance */
try {
    $dbh = new PDO($dsn, $user, $password);
}
catch(PDOException $e) {
    var_dump($e);
    exit("PDO error occured");
}
catch(Exception $e) {
    var_dump($e);
    exit("Error occured");
}


phpinfo();