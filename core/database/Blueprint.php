<?php
/**
 * Class Blueprint
 * @package app\core\database
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

//Assuming that the user is using MYSQL for now
//TODO:: Eloquent model

namespace app\core\database;


use app\core\exception\UserException;

class Blueprint
{

    private string $current = '';
    private array $columns = [];

    private ?string $collation = null;

    /**
     * @return array columns
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    //255 max
    public function string(string $name)
    {
        $this->pushColumn($name,"varchar",255);
        return $this;
    }

    public function id(string $name = 'id')
    {
        $this->bigIncrement($name);
        $this->push("key");
        return $this;
    }

    public function key()
    {
        $this->push("key");
        return $this;
    }

    public function collate(string $collation)
    {
        $this->push($collation,"collate");
        return $this;
    }

    public function collation(string $collation)
    {
        $this->collation = $collation;
    }

    /**
     * @return string|null
     */
    public function getCollation(): ?string
    {
        return $this->collation;
    }

    public function int(string $name)
    {
        $this->pushColumn($name,'int',11);
        return $this;
    }

    public function tinyInt(string $name)
    {
        $this->pushColumn($name,'tint');
        return $this;
    }

    //long
    public function text(string $name)
    {
        $this->pushColumn($name,'text');
        return $this;
    }

    public function auto()
    {
        $this->push("ai");
        return $this;
    }

    //Primay key,Big Integer, 20,Auto increment
    public function bigIncrement(string $name)
    {
        $this->unsignedBigInteger($name);
        $this->push('ai');
        return $this;
    }

    public function increment(string $name)
    {
        return $this->integerIncrement($name);
    }

    public function integerIncrement(string $name)
    {
        $this->unsignedInteger($name);
        $this->push('ai');
        return $this;
    }

    public function smallIncrement(string $name)
    {
        $this->unsignedSmallInteger($name);
        $this->push('ai');
        return $this;
    }

    public function tinyIncrement(string $name)
    {
        $this->unsignedTinyInteger($name);
        $this->push('ai');
        return $this;
    }

    public function unsignedBigInteger(string $name)
    {
        $this->pushColumn($name,'ubi',20);
        return $this;
    }

    public function unsignedInteger(string $name)
    {
        $this->pushColumn($name,'ui',11);
        return $this;
    }

    public function unsignedTinyInteger(string $name)
    {
        $this->pushColumn($name,'uti',4);
        return $this;
    }

    public function unsignedSmallInteger(string $name)
    {
        $this->pushColumn($name,'usi',6);
        return $this;
    }

    public function unsignedMediumInteger(string $name)
    {
        $this->pushColumn($name,'umi',9);
        return $this;
    }

    public function unsignedFloat(string $name)
    {
        $this->pushColumn($name,'uf');
        return $this;
    }

    public function unsignedDecimal(string $name)
    {
        $this->pushColumn($name,'ude');
        return $this;
    }

    public function unsignedDouble(string $name)
    {
        $this->pushColumn($name,'udo');
        return $this;
    }

    public function date(string $name)
    {
        $this->pushColumn($name,"date");
        return $this;
    }

    public function datetime(string $name)
    {
        $this->pushColumn($name,"dtime");
        return $this;
    }

    public function time(string $name)
    {
        $this->pushColumn($name,"time");
        return $this;
    }

    public function year(string $name)
    {
        $this->pushColumn($name,"year");
        return $this;
    }

    public function size(int $size)
    {
        $this->push($size,'size');
        return $this;
    }

    public function nullable()
    {
        $this->push('null');
        return $this;
    }

    public function unique()
    {
        $this->push('unique');
        return $this;
    }

    public function index(string $name)
    {
        if(array_key_exists($name,$this->columns))
            $this->columns[$name][] = "index";
    }

    /**
     * @param string $tableName can be a class or a table name
     * @param string $key reference key
     */
    public function connect(string $tableName, string $key)
    {
        $this->columns[$this->current]['fkey'][] = ["table"=>$tableName,"key"=>$key];
        return $this;
    }

    private function pushColumn(string $name,string $dataType,int $size = null)
    {
        $this->current = $name;
        if($size)
            $this->columns[$name]['size'] = $size;

        return $this->columns[$name][] = $dataType;
    }

    private function push(string $code,string $name = '')
    {
        if ($name)
            return $this->columns[$this->current][$name] = $code;

        return $this->columns[$this->current][] = $code;
    }
}