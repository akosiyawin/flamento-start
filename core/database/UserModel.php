<?php
/**
 * Class UserModel
 * @package app\core\database
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\database;


abstract class UserModel extends Model
{
//    abstract protected function getDisplayName(): string;
    //make non static if error occured

    abstract protected function fields(): array; //return ['firstname','lastname'...]
    abstract public function primaryKey(): string; //return 'key'

    public static function findOne($uniqueValue)
    {
        $table = static::tableName(); //The object, that calls this statically
        $primaryKey = static::primaryKey();
        $sql = "SELECT * FROM {$table} WHERE {$primaryKey} = :{$primaryKey}";
        $stmt = self::prepare($sql);
        $stmt->bindValue(":{$primaryKey}",$uniqueValue);
        self::executeStatement($stmt);
        return $stmt->fetchObject(static::class);
    }

    public static function all()
    {
        $table = static::tableName(); //The object, that calls this statically
        $sql = "SELECT * FROM {$table}";
        $stmt = self::prepare($sql);
        self::executeStatement($stmt);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}



























