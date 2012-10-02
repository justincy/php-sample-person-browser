<?PHP

$env =  json_decode(file_get_contents("/home/dotcloud/environment.json"));
$user = $env->DOTCLOUD_DB_MYSQL_LOGIN;
$password = $env->DOTCLOUD_DB_MYSQL_PASSWORD; 
$host = $env->DOTCLOUD_DB_MYSQL_HOST;
$port = $env->DOTCLOUD_DB_MYSQL_PORT;
$dbname = 'test';