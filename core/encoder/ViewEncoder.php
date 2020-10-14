<?php
/**
 * Class ViewEncoder
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\encoder;

/*
 * Todo: Form important functionalities (enc-type,etc..)*/

use app\core\Application;
use app\core\database\Model;
use app\core\form\Form;

class ViewEncoder extends Encoder
{
    private string $viewContent;

    /**
     * ViewEncoder constructor.
     * @param string $viewContent
     * @param array $params
     * @param string $file
     */

    public function __construct(string $viewContent,array $params = [],string $file)
    {
        parent::__construct($params,$file);
        $this->viewContent = $this->encode($viewContent);
    }

    private function encode(string $content)
    {
        $content = $this->observe($content);
        return $content;
    }

    private function observe(string $content)
    {
        $separator = "\r\n";
        $line = strtok($content, $separator);
        $newContent = '';
        $form = new Form();

        while ($line !== false) {
            $line = str_replace("@endform","</form>",$line);
            $line = str_replace("{{submit()}}",$form->submit(),$line);
            $line = str_replace("@csrf",$this->csrfField(),$line);
            $line = $this->csrfToken($line);

            $line = $this->script($line);
            $line = $this->js($line);
            $line = $this->field($line,$this->params,$this->file) ?? $line;
            $line = $this->form($line,$form);

            $newContent.="$line\n";
            $line = strtok( $separator );
        }
        return $newContent;
    }

    public function __toString()
    {
        return $this->viewContent;
    }

}