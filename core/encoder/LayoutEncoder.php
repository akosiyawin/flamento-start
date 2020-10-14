<?php
/**
 * Class LayoutEncoder
 * @package app\core\encoder
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\encoder;


use app\core\Application;
use app\core\form\Form;

class LayoutEncoder extends Encoder
{
    private string $layout;

    /**
     * LayoutEncoder constructor.
     * @param string $layout
     * @param array $params
     * @param string $file
     */
    public function __construct(string $layout,array $params = [],string $file)
    {
        parent::__construct($params,$file);
        $this->layout = $this->encode($layout);
    }

    private function encode(string $layout)
    {
        $separator = "\r\n";
        $line = strtok($layout, $separator);
        $newContent = '';
        $form = new Form();

        while ($line !== false) {
            /** @var string $decoded {{css('sample/style')}}*/
            $line = $this->css($line);
            $line = $this->style($line);
            $line = $this->script($line);
            $line = $this->js($line);
            $line = $this->csrfToken($line);
            $line = $this->field($line,$this->params,$this->file) ?? $line;
            $line = $this->form($line,$form);
            $newContent.=$line."\n";
            $line = strtok( $separator );
        }
        return $newContent;
    }

    private function css(string $line)
    {
        if(preg_match("/\{\{\s*css\(.+?\)\s*\}\}/", $line,$decoded))
        {
            preg_match("/[\'|\"].*?[\'\"]/",$decoded[0],$path);
            $line = str_replace($decoded,
             "./css/".str_replace(['"',"'"],"",$path[0]).".css",
                    $line);
        }
        return $line;
    }

    public function __toString()
    {
        return $this->layout;
    }

    private function style(string $line)
    {
        if(preg_match("/\{\{\s*style\(.+?\)\s*\}\}/", $line,$decoded))
        {
            preg_match("/[\'|\"].*?[\'\"]/",$decoded[0],$path);
            $line = str_replace($decoded,
             "<link rel='stylesheet' href='./css/".str_replace(['"',"'"],"",$path[0]).".css"."'>",
                    $line);
        }
        return $line;
    }

}