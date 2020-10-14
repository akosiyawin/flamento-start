<?php
/**
 * Class Router
 * @package app\core\route
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\route;


use app\core\Application;
use app\core\console\TerminalController;
use app\core\Controller;
use app\core\exception\NotFoundException;
use app\core\exception\UserException;
use app\core\middlewares\Middleware;

class Router implements RouterFace
{
    private const CONTROLLER_NAMESPACE = "app\\core\\Controller";
    private const HTTP_CONTROLLER_NAMESPACE = "app\\http\\controllers";

    private Request $request;
    private Response $response;

    private array $routes = [];

    /**
     * Router constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->get("/console",[TerminalController::class,"console"]);
        $this->post("/console", [TerminalController::class,"console"]);

    }

    public function get(string $path,$callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post(string $path,$callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve()
    {
        $url = $this->request->getUrl();
        $method = $this->request->getMethod();

        $props_ = [];
        $callback = $this->routes[$method][$url] ?? false;

        if(!$callback)
        {
            $url_ = array_filter(explode("/",$url));
            foreach ($this->routes[$method] as $route => $caller)
            {
                $route_ = array_filter(explode("/",$route));

                if(count($route_) !== count($url_))
                    break;

                for ($i = 1; $i <= count($route_); $i++)
                {
                    if($route_[$i] !== $url_[$i])
                    {
                        if(str_contains($route_[$i],"::"))
                            $props_[$route_[$i]] = $url_[$i];
                        else
                            break;
                    }
                    if($i === count($route_))
                        $callback = $this->routes[$method][$route];
                }
            }
        }

        if(!$callback)
        {
            throw new NotFoundException();
        }

        if(is_string($callback))
        {
            $callback = $this->onString($callback);
        }

        if(is_array($callback))
        {
            $callback = $this->onArray($callback);
        }

        if($props_)
        {
            foreach ($props_ as $prop => $value)
            {
                array_shift($props_);
                $props_[ltrim($prop,":")] = $value;
            }
            return call_user_func($callback,(object) $props_,$this->request,$this->response);
        }

        return call_user_func($callback,$this->request,$this->response);
    }

    private function onArray(array $callback)
    {
        $this->findError($callback);
        /** @var $controller Controller */
        $controller = new $callback[0]();
        Application::$app->controller = $controller;
        $controller->action = $callback[1];
        $callback[0] = $controller; //Will make the callback an actual object instead of string
        /** @var $middleware Middleware*/
        foreach ($controller->getMiddlewares() as $middleware)
        {
            $middleware->execute();
        }
        return $callback;
    }

    private function onString(string $callback)
    {
        $callback = explode("@",$callback);
        $callback[0] = self::HTTP_CONTROLLER_NAMESPACE."\\".$callback[0];

        $this->findError($callback);

        /** @var $controller Controller */
        $controller = new $callback[0]();
        Application::$app->controller = $controller;
        $controller->action = $callback[1];

        foreach ($controller->getMiddlewares() as $middleware)
        {
            $middleware->execute();
        }

//        $callback[0] = $controller; //Will make the callback an actual object instead of string, We need string here
        return $callback;
    }

    private function findError(array $callback)
    {
        if(count($callback) > 2)
        {
            throw new UserException(
                "Too many arguments supplied in array, consider following the format:".PHP_EOL.
                "[ExampleController::class,'method'] or ExampleController@method"
                ,400);
        }

        if(count($callback) < 2)
        {
            throw new UserException(
                "Lack of arguments supplied in array, consider following the format:".PHP_EOL.
                "[ExampleController::class,'method'] or ExampleController@method"
                ,400);
        }

        if(!class_exists($callback[0]))
        {
            throw new UserException(
                "Controller does not exist: ". (string) $callback[0]
                ,404);
        }

        if(!is_subclass_of($callback[0],self::CONTROLLER_NAMESPACE))
        {
            throw new UserException(
                "Controller <i>". $callback[0] ."</i> is not an instance of <b>". self::CONTROLLER_NAMESPACE ."</b>" .PHP_EOL.
                "consider the following format: ExampleController extends ". self::CONTROLLER_NAMESPACE
                ,400);
        }
        if(!method_exists($callback[0],$callback[1]))
        {
            throw new UserException(
                "Method does not exist: '<b>". $callback[1] ."</b>' in <i>". $callback[0] ."</i>"
                ,404);
        }
    }
}