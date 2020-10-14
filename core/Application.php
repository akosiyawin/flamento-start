<?php
/**
 * Class Application
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core;


use app\core\database\Database;
use app\core\route\Request;
use app\core\route\Response;
use app\core\route\Router;

class Application
{
    public static Application $app; //Singleton
    public static string $rootDir;
    private Request $request;
    public Response $response;
    public Router $route;
    public Controller $controller;
    public View $view; //Usage in Base Controller
    public Database $db;
    public Session $session;
    public Auth $auth;

    function __construct(string $rootpath,array $config = [])
    {
        self::$app = $this;
        self::$rootDir = $rootpath;
        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Router($this->request,$this->response);
        $this->controller = new Controller();
        $this->view = new View($config['console']);
        $this->db = new Database($config['db']);
        $this->session = new Session();
        $this->auth = new Auth($this->session,$config['userAuth']);
    }

    public function start()
    {
        try {
            echo $this->route->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            $this->controller->setLayout("_error"); //Uncatch error
            echo $this->view->defaultRenderView("_error",[
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        }
    }

    public function __destruct()
    {
        $this->start();
    }

}