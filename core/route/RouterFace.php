<?php
/**
 * Interface RouterFace
 * @package app\core\faces
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\route;

/*
 * Required notice
 * */
interface RouterFace
{
    public function get(string $path, $callback);
    public function post(string $path, $callback);
}