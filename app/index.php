<?PHP

/* Read the environment.json file */
if(file_exists("/home/dotcloud/environment.json")) {
    /* configuration on dotCloud */
    require('env.php');
    
    $dsn = "mysql:dbname=$dbname;host=$host;port=$port";
}
else {
    /* your local configuration */
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
    exit("PDO error occurred");
}
catch(Exception $e) {
    var_dump($e);
    exit("Error occurred");
}

?>
<div id='content' data-stack="php" data-appname="<?PHP echo $env->DOTCLOUD_PROJECT?>">
    Hello World, from PHP!
</div>
<script type="text/javascript" src="https://helloapp.dotcloud.com/inject.min.js"></script>