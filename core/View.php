<?php
/**
 * Class View
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core;


use app\core\encoder\LayoutEncoder;
use app\core\encoder\ViewEncoder;
use app\core\exception\UserException;

class View
{
    private const VIEW_EXTENSION = "php";
    private string $console;

    public function __construct(string $console)
    {
        $this->console = strtolower($console) === "on" ?? false;
    }

    public function renderView(string $view,array $params = [])
    {
        $file = Application::$rootDir ."/resources/views/".str_replace(".","/",$view).".". self::VIEW_EXTENSION;
        $viewContent = $this->renderOnlyView($view,$params);
        $viewContent = new ViewEncoder($viewContent,$params,$file);
        $layoutContent = $this->layoutContent();
        $layoutContent = new LayoutEncoder($layoutContent,$params,$file);
        return str_replace("{{content}}",$viewContent,$layoutContent);
    }

    #Console before
  /*  public function renderConsole()
    {
        $file = Application::$rootDir ."/core/console/_console.php";
        $viewContent = $this->renderOnlyConsole();
        $layoutContent = $this->layoutOfConsole();
        return str_replace("{{content}}",$viewContent,$layoutContent);
    }

    private function renderOnlyConsole()
    {
        $file = Application::$rootDir ."/core/console/_console.php";
        if(!file_exists($file))
            throw new UserException("Console doesnt exist in views. Console not found!",404);

        ob_start(); //start remembering
        include_once $file; //save everything to buffer
        return ob_get_clean(); //get and clean the buffer, now we can assign everything into a variable, this will return string.
    }

    private function layoutOfConsole()
    {
        ob_start();
        include_once Application::$rootDir ."/core/console/_console.php";
        return ob_get_clean();
    }*/

    private function layoutContent()
    {
        $layout = Application::$app->controller->layout;
        $file = Application::$rootDir ."/resources/layouts/$layout.". self::VIEW_EXTENSION;

        if(!file_exists($file))
            throw new UserException("$layout doesnt exist on layouts",404);

        ob_start();
        include_once $file;
//        <base href="http://localhost/phpmvc/public/">

        $layoutOB = ob_get_clean();

        if($this->console) //hotkeys for console
        {
            $layoutOB = str_replace("</body>","<script src='app.js'></script>\r\n</body>",$layoutOB);
        }

        $absPath = substr(Application::$rootDir,strrpos(Application::$rootDir,"\\")+1);

        if(RUNNING_ON_SERVE)
            return str_replace("<head>","<head>\r\n<base href=\"http://localhost:8080/\">",$layoutOB);

        return str_replace("<head>","<head>\r\n<base href=\"http://localhost/{$absPath}/public/\">",$layoutOB);
    }

    private function renderOnlyView(string $view, array $params = [])
    {
        foreach ($params as $key => $value)
        {
            $$key = $value;
        }
        $file = Application::$rootDir ."/resources/views/".str_replace(".","/",$view).".". self::VIEW_EXTENSION;
        if(!file_exists($file))
            throw new UserException("$view doesnt exist on views",404);

        ob_start(); //start remembering
        include_once $file; //save everything to buffer
        return ob_get_clean(); //get and clean the buffer, now we can assign everything into a variable, this will return string.
    }

    public function defaultRenderView(string $view,array $params = [])
    {
        $viewContent = $this->defaultRenderOnlyView($view,$params);
        $layoutContent = $this->defaultLayoutContent();

        return str_replace("{{content}}",$viewContent,$layoutContent);
    }

    private function defaultLayoutContent()
    {
        $layout = Application::$app->controller->layout;

        $file = Application::$rootDir ."/resources/default/layouts/$layout.". self::VIEW_EXTENSION;
        if(!file_exists($file))
            throw new UserException("$layout doesnt exist on default layouts",404);

        ob_start();
        include_once $file;
        return ob_get_clean();
    }

    private function defaultRenderOnlyView(string $view, array $params = [])
    {
        foreach ($params as $key => $value)
        {
            $$key = $value;
        }
        $file = Application::$rootDir ."/resources/default/views/$view.". self::VIEW_EXTENSION;
        if(!file_exists($file))
            throw new UserException("$view doesnt exist on default views",404);

        ob_start(); //start remembering
        include_once $file; //save everything to buffer
        return ob_get_clean(); //get and clean the buffer, now we can assign everything into a variable, this will return string.
    }
}