<?php
/**
 * Class Schema
 * @package app\core\database
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */
//Assuming that the user is using MYSQL for now

namespace app\core\database;

use app\core\Application;
use app\core\Bull;
use app\database\models\User;

abstract class Schema
{

    /*
     * Todo: Create a better and simple way of creating table, with Blueprints
     * */
    public static function create(string $sql)
    {
        $db = Application::$app->db;
        $db->executeStatement($db->prepare($sql));
    }

    private static function buildQuery(string $tableName,Blueprint $blueprint)
    {

        $keys = [];
        $indexes = [];
        $fkeys = [];

        $query = "CREATE TABLE `{$tableName}` (";
        foreach($blueprint->getColumns() as $column => $props)
        {
            $query .= "`{$column}`";
            $hasSize = isset($props['size']) ? "({$props['size']})" : "";
            if(in_array("varchar",$props))
                $query .= " VARCHAR{$hasSize}";

            if(in_array("int",$props))
                $query .= " INT{$hasSize}";

            if(in_array("tint",$props))
                $query .= " TINYINT{$hasSize}";

            if(in_array("text",$props))
                $query .= " TEXT{$hasSize}";

            if(in_array("ubi",$props))
                $query .= " BIGINT{$hasSize} UNSIGNED";

            if(in_array("ui",$props))
                $query .= " INT{$hasSize} UNSIGNED";

            if(in_array("uti",$props))
                $query .= " TINYINT{$hasSize} UNSIGNED";

            if(in_array("usi",$props))
                $query .= " SMALLINT{$hasSize} UNSIGNED";

            if(in_array("umi",$props))
                $query .= " MEDIUMINT{$hasSize} UNSIGNED";

            if(in_array("uf",$props))
                $query .= " FLOAT{$hasSize} UNSIGNED";

            if(in_array("ude",$props))
                $query .= " DECIMAL{$hasSize} UNSIGNED";

            if(in_array("udo",$props))
                $query .= " DOUBLE{$hasSize} UNSIGNED";

            if(in_array("date",$props))
                $query .= " DATE";

            if(in_array("dtime",$props))
                $query .= " DATETIME";

            if(in_array("time",$props))
                $query .= " TIME";

            if(in_array("year",$props))
                $query .= " YEAR";

            if(in_array("ai",$props))
                $query .= " AUTO_INCREMENT";

            if(in_array("unique",$props))
                $query .= " UNIQUE";

            if(in_array("key",$props))
                $keys[] = $column;

            if(in_array("index",$props))
                $indexes[] = $column;

            if(array_key_exists("collate",$props))
                $query .= " COLLATE {$props['collate']}";

            if(array_key_exists("fkey",$props))
            {
                $fkeys[$column] = $props['fkey'];
            }

            if (in_array("null",$props))
                $query .= " DEFAULT NULL,";
            else
                $query .= " NOT NULL,";
        }

        if($keys)
        {
            $pKey = implode(",",$keys);
            $query .= " PRIMARY KEY ($pKey),";
        }

        if($fkeys)
        {
            foreach($fkeys as $key => $attributes)
            {
                foreach ($attributes as $attr)
                {
                    if(class_exists($attr['table']))
                    {
                        /**
                         * @var $getTableName Model
                         */
                        $getTableName = $attr['table']::tableName();
                        $query .= " FOREIGN KEY (`{$key}`) REFERENCES `{$getTableName}` (`{$attr['key']}`),";
                        continue;
                    }
                    $query .= " FOREIGN KEY (`{$key}`) REFERENCES `{$attr['table']}` (`{$attr['key']}`),";
                }
            }
        }

        if($indexes)
        {
            $indexes = array_map(function ($item){
                return " INDEX (`{$item}`)";
            },$indexes);
            $query .=  implode(",",$indexes);
        }

        $query = trim($query,",").")";

        if($blueprint->getCollation())
        {
            $charset = substr($blueprint->getCollation(),0,strpos($blueprint->getCollation(),"_"));
            $query .= " DEFAULT CHARSET={$charset} COLLATE={$blueprint->getCollation()}";
        }

        return $query;
    }

    public static function build(string $tableName,callable $callback)
    {
        $blueprint = new Blueprint();
        call_user_func($callback, $blueprint);

        $query = self::buildQuery($tableName,$blueprint);
        $db = Application::$app->db;
        $db->executeStatement($db->prepare($query));
    }

    public static function dropIfExists(string $table)
    {
        $db = Application::$app->db;
        $db->executeStatement($db->prepare("DROP TABLE IF EXISTS $table"));
    }

    public static function alterTable(string $sql)
    {
        $db = Application::$app->db;
        $db->executeStatement($db->prepare($sql));
    }
}