<?php
/**
 * Class Controller
 * @package app\core\route
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core;


use app\core\middlewares\Middleware;

class Controller
{
    public string $layout = "main";
    public string $action = ''; //The action/method is in string

    /** @var $middlewares Middleware[] */
    protected array $middlewares = [];

    /**
     * @return Middleware[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setLayout(string $layout)
    {
        $this->layout = $layout;
    }

    protected function render(string $view,array $params = [])
    {
        return Application::$app->view->renderView($view,$params);
    }

    public function registerMiddleware(Middleware $middleware)
    {
        return $this->middlewares[] = $middleware;
    }

}