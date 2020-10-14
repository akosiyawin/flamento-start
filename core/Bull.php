<?php
/**
 * Class Bull
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core;


abstract class Bull
{
    public const TYPE_TEXT = "text";
    public const TYPE_EMAIL = "email";
    public const TYPE_PASSWORD = "password";
    public const TYPE_NUMBER = "number";

    public const NAMESPACE_DATABASE_MODELS = "app\\database\\models";
    public const NAMESPACE_CORE_DATABASE_MODEL = "app\\core\\database\\Model";
    public const NAMESPACE_DATABASE_MIGRATIONS = "app\\database\\migrations";
    public const NAMESPACE_CORE_DATABASE_MIGRATION = "app\\core\\database\\Migration";

    public const NAMESPACE_HTTP_CONTROLLERS = "app\\http\\controllers";

    public const NAMESPACE_HTTP_MIDDLEWARES = "app\\http\\middlewares";
    public const NAMESPACE_CORE_MIDDLEWARES_MIDDLEWARE = "app\\core\\middlewares\\Middleware";

    /*public const Q_COLUMN = "column";
    public const Q_CONDITION = "condition";
    public const Q_GREATER_THAN = ">";
    public const Q_LESS_THAN = ">";
    public const Q_VALUE = "value";*/

    public const FIND_TYPE_ALL = "all";
    public const FIND_TYPE_ONE = "one";



    /**
     * @var string $path path where to search the string
     * @var string $search the string to search on first placement
     * @return int The line number of searched string*/
    public static function findLineNumber(string $path,string $search)
    {
        $lineNumber = false;

        if ($handle = fopen($path, "r")) {
            $count = 0;
            while (($line = fgets($handle, 4096)) !== FALSE and !$lineNumber) {
                $count++;
                $lineNumber = (strpos($line, $search) !== FALSE) ? $count : $lineNumber;
            }
            fclose($handle);
        }

        return $lineNumber;
    }

    public static function formatNumber($number,int $zeroCounts)
    {
        /*if(strlen($number) > $zeroCounts)
        {
            Notice::message("Invalid value length of number > zero counts.");
        }*/

        $output = "";
        for ($i = 0; $i < $zeroCounts; $i++)
        {
            $output.='0';
        }

        $output = substr($output,0,strlen($output) - strlen($number)).$number;

        return $output;
    }

    function isDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }


    protected function hash(string $str)
    {
        return password_hash($str,PASSWORD_DEFAULT);
    }
    protected function verify(string $str,string $hash)
    {
        return password_verify($str,$hash);
    }

}