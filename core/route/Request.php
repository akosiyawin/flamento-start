<?php
/**
 * Class Request
 * @package app\core\route
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\route;


use app\core\Application;
use app\core\exception\ForbiddenException;

class Request
{

    public function getUrl()
    {
        if(isset($_GET['_url']))
        {
            define("RUNNING_ON_SERVE",false);
            $url = $_GET['_url'];
        }
        else
        {
            define("RUNNING_ON_SERVE",true);
            $url = $_SERVER['REQUEST_URI'];
            if(substr_count(rtrim($url,"/"),"/") === 0)
            {
                $url = "/";
            }
        }

        if(strpos($url,"?"))
            return substr($url,0,strpos($url,"?"));

        return $url;
    }

    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet()
    {
        return $this->getMethod() === "get";
    }

    public function isPost()
    {
        return $this->getMethod() === "post";
    }

    public function body()
    {
        $data = [];
        unset($_GET['_url']); //to remove the url attributes
        if($this->getMethod() === "get")
        {
            if($_ENV['CSRF_GET_REQUEST'] === "on" && $_ENV['CSRF'] === "on")
                $this->verifyTokenFirst();

            foreach ($_GET as $key => $value)
            {
                $data[$key] = filter_input(INPUT_GET,$key,FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if($this->getMethod() === "post")
        {
            if($_ENV['CSRF_POST_REQUEST'] === "on" && $_ENV['CSRF'] === "on")
                $this->verifyTokenFirst();

            foreach ($_POST as $key => $value)
            {
                $data[$key] = filter_input(INPUT_POST,$key,FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $data;
    }

    public function but(array $removes)
    {
        $body = $this->body();
        $newBody = [];

        foreach ($body as $key => $value)
        {
            foreach ($removes as $remove)
            {
                if (strtolower($remove) === strtolower($key))
                {
                    continue 2;
                }
            }
                $newBody[$key] = $value;
        }
        return $newBody;
    }

    public function authorizedOnly(array $fields)
    {
        $body = $this->body();
        $newBody = [];
        $fields = array_map("strtolower",$fields);
        foreach ($body as $key => $value)
        {
            if(in_array(strtolower($key),array_change_key_case($fields)))
            {
                $newBody[$key] = $value;
            }
        }
        return $newBody;
    }

    private function verifyTokenFirst()
    {
        if(!isset($_REQUEST['_token']) || !$_REQUEST['_token'])
        {
            throw new ForbiddenException("Token not not found, This is forbidden");
        }

        Application::$app->session->verifyToken($_REQUEST['_token']);
    }

}