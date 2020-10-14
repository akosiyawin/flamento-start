<?php
/**
 * Class TerminalMiddleware
 * @package app\core\console
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\console;


use app\core\middlewares\Middleware;

class TerminalMiddleware extends Middleware
{

    public function execute()
    {
        if($_ENV['CONSOLE'] !== "on")
        {
            $this->authenticate();
        }
    }
}