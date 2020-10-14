<?php
/**
 * Class Notice
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core;


abstract class Notice
{
    public static function message(string $message)
    {
        echo '<pre>';
        echo $message;
        echo '</pre>';
    }

    public static function messageOnDie(string $message)
    {
        echo '<pre>';
        echo $message;
        echo '</pre>';
        die;
    }

    public static function push(string $message,string $paragraph,string $path,string $lineNumber)
    {
        echo("
            <h5>Notice Message</h5>
            $message
            
            <h5>Paragraph</h5>
            <pre>".trim(htmlspecialchars($paragraph))."</pre>
            
            <h5>Path</h5>
            $path
            
            <h5>Line</h5>
            $lineNumber
        ");
    }




}