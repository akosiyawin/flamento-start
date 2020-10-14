<?php
/**
 * Class ForbiddenException
 * @package app\core\exception
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\exception;


class ForbiddenException extends \Exception
{
    protected $message = "Access to this site is strictly Forbidden";
    protected $code = 403;
}