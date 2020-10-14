<?php
/**
 * Class Response
 * @package app\core\route
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\route;


class Response
{
    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function redirect(string $url)
    {
        header("Location: $url");
    }

    public function send($message, $status = 200){
        header("HTTP/1.1 $status");
        header("Content-Type:application/json");
        //need to add this if running on serve
//        header('Access-Control-Allow-Origin: *'); //There's a downside for this
        echo json_encode($message);
//        if($status >= 400)
//        {
//            array_unshift($message,['status'=>'error']);
//            die();
//        }
    }

}