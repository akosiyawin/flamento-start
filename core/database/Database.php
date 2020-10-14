<?php
/**
 * Class Database
 * @package app\core\database
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\database;


use app\core\Application;
use Cassandra\Statement;

class Database
{
    private \PDO $pdo;

    private array $logs = [];

    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? "";
        $user = $config['user'] ?? "";
        $pwd = $config['password'] ?? "";

        /*
         * Error: Please configure your .env file set your database and host settings*/
        $this->pdo = new \PDO($dsn,$user,$pwd);
        //W/o this line we cant detect any error(exceptions throw) from pdo.
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
    }

    public function prepare(string $sql)
    {
        return $this->pdo->prepare($sql);
    }

    public function execute(string $sql)
    {
        return $this->pdo->exec($sql);
    }

    public function executeStatement(\PDOStatement $stmt)
    {
        return $stmt->execute();
    }

    /*Todo: make revert Migration method*/
    public function applyMigrations(bool $fromController = false)
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();
        $newMigrations = [];

        $files = scandir(Application::$rootDir."/database/migrations");

//        $toApplyMigrations = array_diff($files,$appliedMigrations); //use only if error occured
        $toApplyMigrations = array_diff(array_map(fn($f) => trim($f,".php"),$files),$appliedMigrations);

        foreach ($toApplyMigrations as $migration) {
            if($migration === '.' or $migration === '..' or empty($migration))
            {
                continue;
            }
            require_once Application::$rootDir."/database/migrations/$migration.php";
            $className = substr(pathinfo($migration,PATHINFO_FILENAME),7);
            $class = "app\\database\\migrations\\".$className;

            /** @var $instance Migration*/
            $instance = new $class();
            $this->logs("Applying migration $className",$fromController);
            try
            {
                $instance->up();
            }
            catch (\Exception $exception){
                $this->logs("Failed to apply migration <strong>{$className}</strong><br>{$exception->getMessage()}",$fromController);
                continue;
            }

            $this->logs("Migration $className has been applied successfully",$fromController);
            $newMigrations[] = $migration;
        }

        if(!empty($newMigrations))
        {
            $this->saveNewMigrations($newMigrations);
        }else
        {
//            Application::$app->response->response(['message'=>'All migrations are applied']);
            $this->logs("All migrations are applied",$fromController);
        }

        if($fromController)
            return Application::$app->response->send(["messages"=>$this->logs]);

    }

    public function rollback(string $step)
    {
        if(!is_numeric($step))
            $this->logs("Invalid rollback value",true);

        $appliedMigrations = $this->getAppliedMigrations();
        rsort($appliedMigrations);

        if(count($appliedMigrations) === 0)
        {
            $this->logs("Roll back failed, No migrations found!",true);
            return Application::$app->response->send(["messages"=>$this->logs]);
        }

        if(count($appliedMigrations) < $step)
        {
            $this->logs("Invalid rollback steps, you can only roll back up to ".count($appliedMigrations),true);
            return Application::$app->response->send(["messages"=>$this->logs]);
        }


        if($step == 0)
            $step = count($appliedMigrations);

        for ($i = 0; $i < $step; $i++)
        {
            $this->logs("Processing migration roll back on step ". ($i+1) ."...",true);
            $this->logs("Dropping migration '{$appliedMigrations[$i]}'....",true);
            if($this->dropMigration($appliedMigrations[$i]))
            {
                $this->logs("Migration '{$appliedMigrations[$i]}' has been dropped successfully",true);
            }
            else
                $this->logs("Failure encountered while dropping migration '{$appliedMigrations[$i]}'",true);
        }

        return Application::$app->response->send(["messages"=>$this->logs]);
    }

    private function saveNewMigrations(array $migrations)
    {
        $migrations = array_map(fn($m) => "('".trim($m,".php")."')",$migrations);
        $str = implode(",",$migrations);

        $stmt = $this->prepare("INSERT INTO migrations (migration_name) VALUES $str");
        return $this->executeStatement($stmt);
    }

    private function getAppliedMigrations()
    {
        /*
         * Todo: Fix if the migration_name col is not found, R: maybe another table found
         * */
        $stmt = $this->prepare("SELECT migration_name FROM migrations");
        $this->executeStatement($stmt);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function dropMigration(string $migration)
    {
        require_once Application::$rootDir."/database/migrations/$migration.php";
        $className = substr(pathinfo($migration,PATHINFO_FILENAME),7);
        $class = "app\\database\\migrations\\".$className;

        /** @var $instance Migration*/
        $instance = new $class();
        try
        {
            $instance->down();
            $stmt = $this->prepare("DELETE FROM migrations WHERE migration_name = :name");
            $stmt->bindValue(":name",$migration);
            $this->executeStatement($stmt);
            return true;
        }
        catch (\Exception $exception){
            return false;
        }
    }

    private function createMigrationsTable()
    {
        /*
         * currently this sql is defined for mysql phpmyadmin
         * */
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
                ENGINE = INNODB;";
        $stmt = $this->prepare($sql);
        return $stmt->execute();
    }

    private function logs(string $message,bool $fromController = false)
    {
        if($fromController)
            return $this->logs[] = sprintf("[%s] - %s",date("Y-m-d H:i:s"),$message).PHP_EOL;

        echo sprintf("[%s] - %s",date("Y-m-d H:i:s"),$message).PHP_EOL;
    }


}