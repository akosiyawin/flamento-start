<?php
/**
 * Class UserException
 * @package app\core\exception
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\exception;


class UserException extends \Exception
{
    public const EXCEPTION_TYPE_DIE = 'die';
    protected $message = "User Exception";
    protected $code = 400;
    public string $errorType = self::EXCEPTION_TYPE_DIE;
}