<?php
/**
 * Class NotFoundException
 * @package app\core\exception
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\exception;


class NotFoundException extends \Exception
{
    protected $message = "Not Found";
    protected $code = 404;
}