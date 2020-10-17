<?php
use flamist\package\Application;

require_once dirname(__DIR__)."/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load(); //Environment must load first.

$config = [
    'db' => [
        'dsn' => $_ENV['DB_DSN'],
        'user' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
    'console' => $_ENV['CONSOLE'],
    'userAuth' => ''
];

$app = new Application(dirname(__DIR__),$config);
$app->route->get("/",function (){
    return "Hello Flamentist";
});

//$app->start();//Must run lastly