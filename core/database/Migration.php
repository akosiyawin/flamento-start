<?php

/**
 * Interface Migration
 * @package app\core\database
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\database;

interface Migration
{
    public function up();
    public function down();
}