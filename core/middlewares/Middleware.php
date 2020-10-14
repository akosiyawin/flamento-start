<?php
/**
 * Class Middleware
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\middlewares;


use app\core\Application;
use app\core\exception\ForbiddenException;

abstract class Middleware
{
    protected array $registeredActions = [];
    abstract public function execute();

    /**
     * Middle constructor.
     * @param array $actions
     * if actions is empty, it will restrict the whole class
     */
    public function __construct(array $actions = [])
    {
        $this->registeredActions = $actions;
    }

    protected function authenticate()
    {
        /*
            *We are going to check if 'action' is in ['action1','action2]]*/
        if(empty($this->registeredActions) || in_array(Application::$app->controller->action,$this->registeredActions))
        {
            throw new ForbiddenException();
        }
    }
}