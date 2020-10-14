<?php
use \app\http\controllers\AuthController;
use \app\http\controllers\AdminController;
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
    'userAuth' => \app\database\models\User::class
];


$app = new Application(dirname(__DIR__),$config);
$app->route->get("/login",[AuthController::class,"login"]);
$app->route->post("/login",[AuthController::class,"login"]);
$app->route->post("/update/p/user/status",[AdminController::class,"updateStatus"]);
$app->route->post("/update/p/user/role",[AdminController::class,"updateRole"]);
$app->route->post("/admin/p/signout",[AdminController::class,"signout"]);

$app->route->get("/admin/dashboard","AdminController@dashboard");
$app->route->get("/admin/get/users","AdminController@getUsers");
$app->route->get("/admin/get/offices","AdminController@getOffices");
$app->route->post("/add/post/user","AdminController@addUser");
$app->route->get("/admin/get/users/by/office","AdminController@getUsersByOffice");


//$app->start();//Must run lastly