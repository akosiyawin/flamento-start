<?php
/**
 * Class Encoder
 * @package app\core\encoder
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\encoder;


use app\core\Application;
use app\core\form\Form;

abstract class Encoder
{
    protected array $params;
    protected string $file;

    public function __construct(array $params = [],string $file)
    {
        $this->params = $params;
        $this->file = $file;
    }

    protected function script(string $line)
    {
        if(preg_match("/\{\{\s*script\s*\(.+?\)\s*\}\}/", $line,$decoded))
        {
            preg_match("/[\'|\"].*?[\'\"]/",$decoded[0],$path);
            $line = str_replace($decoded,
                "<script src='./js/".str_replace(['"',"'"],"",$path[0]).".js"."'></script>"
                ,$line);
        }
        return $line;
    }

    protected function js(string $line)
    {
        if(preg_match("/\{\{\s*js\s*\(.+?\)\s*\}\}/", $line,$decoded))
        {
            preg_match("/[\'|\"].*?[\'\"]/",$decoded[0],$path);
            $line = str_replace($decoded,
                "./js/".str_replace(['"',"'"],"",$path[0]).".js".""
                ,$line);
        }
        return $line;
    }

    protected function field(string $line, array $params, string $file)
    {
        if(preg_match_all("/\{\{\s*field.+?\}\}/", $line,$decoded))
        {
            foreach ($decoded[0] as $decode)
            {
                $field = str_replace(['{{','}}'],"",$decode);
                foreach ($params as $key => $paramValue)
                {
                    $$key = $paramValue;
                }

                try {
                    /** @var $decode {field($user,'email','text')}*/
                    $success = eval("\$line = str_replace(\$decode,\app\core\\form\Form::$field,\$line);");
                    return $line;
                }catch (\ParseError $error)
                {
                    $paragraph = $error->getTrace()[2]['args'][0];
                    Notice::push($error->getMessage(),$paragraph,$file,Bull::findLineNumber($file,$line));
                    continue;
                }
                return false;
            }
        }
    }

    protected function form(string $line,Form $form)
    {
        if(preg_match("/@form\s*\(.+\)/", $line))
        {
            preg_match_all("/[\'|\"].*?[\'\"]/",$line,$params);
            $action = trim($params[0][0],"\"\'");
            $method = trim($params[0][1],"\"\'");
            $line = str_replace($line, $form->beginString($action,$method),$line);
        }
        return $line;
    }

    protected function csrfToken(string $line)
    {
        return str_replace("{{csrf_token()}}",Application::$app->session->get('csrf_token'),$line);
    }

    protected function csrfField()
    {
        return "<input type='hidden' name='_token' value='".Application::$app->session->get('csrf_token')."'>";
    }
}

/*   protected function csrfToken(string $line)
   {
       $fileName = substr($this->file,strrpos($this->file,"/")+1);
       return str_replace("{{csrf_token()}}",Application::$app->session->makeToken($fileName),$line);
   }

   protected function csrfField()
   {
       $fileName = substr($this->file,strrpos($this->file,"/")+1);
//        $fileName = substr($fileName,0,strpos($fileName,".php"));
       return "<input type='hidden' name='_token' value='".Application::$app->session->makeToken($fileName)."'>";
   }*/

/* protected function csrfField()
 {
     dd($this->file);
     return "<input type='hidden' name='_token' value='".Application::$app->session->get('csrf_token')."'>";
 }*/