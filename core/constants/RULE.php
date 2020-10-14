<?php
/**
 * Class RULE
 * @package app\core\constants
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\constants;


abstract class RULE
{
    public const REQUIRED = "required";
    public const EMAIL = "email";
    public const MAX = "max";
    public const MIN = "min";
    public const MATCH = "match";
    public const UNIQUE = "unique";
}

