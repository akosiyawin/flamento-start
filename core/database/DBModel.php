<?php
/**
 * Class DBModel
 * @package app\core\database
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\database;


use app\core\Application;
use app\core\Bull;

abstract class DBModel
{
    private static string $sql = '';
    private static array $attributes = [];
    private static string $findType;
    private static array $removeLists = [];

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        dd("Im first");
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        dd("Im first");
    }

    public function sayHi()
    {
        echo "hi";
    }

    public static function hi(string $tar)
    {
        echo "hi";
    }

    public function save()
    {
        $table = $this->tableName();
        $fields = $this->fields();
        $params = array_map(fn($p) => ":$p",$fields);
        $sql = "INSERT INTO $table (".implode(",",$fields).") VALUES (".implode(",",$params).")";
        $stmt = $this->prepare($sql);
        foreach ($fields as $field)
        {
            $stmt->bindValue(":$field",$this->{$field});
        }

        $this->executeStatement($stmt);
        return true;
    }


    protected function prepare(string $sql)
    {
        return Application::$app->db->prepare($sql);
    }

    protected function executeStatement(\PDOStatement $statement)
    {
        return Application::$app->db->executeStatement($statement);
    }

    private static function refresh()
    {
        self::$attributes = []; //To refresh the static access
        self::$sql = '';
    }

    public static function findOneWhere(string $column,string $condition, $value) //[email => dawin@mail.com, firstname => Darwin]
    {
        self::refresh();
        self::$findType = Bull::FIND_TYPE_ONE;
        $table = static::tableName(); //The object, that calls this statically
        self::$sql.= "SELECT * FROM {$table} WHERE {$column} {$condition} ?";
        self::$attributes[] = $value;
        return new static();
    }

    public static function findAll()
    {
        self::$findType = Bull::FIND_TYPE_ALL;
        self::refresh();
        $table = static::tableName();
        self::$sql = "SELECT * FROM {$table}";
        return new static();
    }

    public static function update(string $column, string $value)
    {
        self::refresh();
        $table = static::tableName();
        self::$sql = "UPDATE {$table} SET {$column} = ?";
        self::$attributes[] = $value;
        return new static();
    }

    public function set()
    {
        $stmt = $this->prepare(self::$sql);
        foreach (self::$attributes as $key => $value)
        {
            $stmt->bindValue(++$key,$value);
        }
        return $this->executeStatement($stmt);
    }

    public static function findOnly(array $columns)
    {
        self::refresh();
        self::$findType = Bull::FIND_TYPE_ALL;
        $table = static::tableName();
        $stacks = [];
        foreach ($columns as $column => $value)
        {
            if(!is_numeric($column))
            {
                $stacks[] = "{$column} AS $value";
                continue;
            }
                $stacks[] = $value;
        }
        $strStack = implode(",",$stacks);
        self::$sql = "SELECT {$strStack} FROM {$table}";
        return new static();
    }

    public static function findWhere(string $column,string $condition, $value)
    {
        self::refresh();
        self::$findType = Bull::FIND_TYPE_ALL;
        $table = static::tableName();
        self::$sql .= "SELECT * FROM {$table} WHERE {$column} {$condition} ?";
        self::$attributes[] = $value;
        return new static();
    }

    public function where(string $column,string $condition, $value)
    {
        self::$sql .= " WHERE {$column} {$condition} ? ";
        self::$attributes[] = $value;
        return new static();
    }

    public function join(string $table, string $fkey, string $pkey)
    {
        self::$sql .= " INNER JOIN {$table} ON {$fkey} = {$pkey}";
        return new static();
    }

    public function and(string $column,string $condition, $value)
    {
        self::$sql .= " AND {$column} {$condition} ? ";
        self::$attributes[] = $value;
        return new static();
    }

    /**
     * @var $column
     * can be array | string
     */
    public function except($column)
    {
        self::$removeLists[] = $column;

        if(is_array($column))
        {
            self::$removeLists = $column;
        }
        return new static();
    }

    public function get()
    {
        $stmt = $this->prepare(self::$sql);
        foreach (self::$attributes as $key => $value)
        {
            $stmt->bindValue(++$key,$value);
        }
        $this->executeStatement($stmt);

        if(self::$findType === Bull::FIND_TYPE_ONE)
        {
            $result = $stmt->fetchObject(static::class);
            if(!empty(self::$removeLists))
            {
                foreach (self::$removeLists as $value)
                {
                    unset($result->{$value});
                }
            }
            return $result;
        }

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(!empty(self::$removeLists))
        {
            foreach (self::$removeLists as $value)
            {
                foreach ($results as $count => $key)
                {
                    unset($results[$count][$value]);
                }
            }
        }
        return $results;
    }

}

/*

public function get()
{

    $stmt = $this->prepare($this->sql);
    foreach ($this->attribute as $key => $value)
    {
        $stmt->bindValue(++$key,$value);
    }
    $this->executeStatement($stmt);

    if($this->findType === Bull::FIND_TYPE_ONE)
    {
        return $stmt->fetchObject(static::class);
    }

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}*/
/*
public function findWhere(string $column,string $condition, $value)
{
    $this->findType = Bull::FIND_TYPE_ALL;
    $table = static::tableName();
    $this->sql .= "SELECT * FROM {$table} WHERE {$column} {$condition} ?";
    $this->attribute[] = $value;
    return $this;
}*/