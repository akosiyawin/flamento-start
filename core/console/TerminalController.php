<?php
/**
 * Class TerminalController
 * @package app\http\console
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\console;


use app\core\Application;
use app\core\Bull;
use app\core\Controller;
use app\core\database\Schema;
use app\core\route\Request;
use app\core\route\Response;

class TerminalController extends Controller
{

    private const AUTHOR = "Darwin Marcelo <akosiyawin@gmail.com>";

    public function __construct()
    {
        $this->registerMiddleware(new TerminalMiddleware(['console']));
    }

    public function console(Request $request,Response $response)
    {
        if($request->isPost())
        {
            $command = trim($request->body()['command']);
            return $this->handleCommand($command);
        }
        $this->setLayout("_console");
        return Application::$app->view->defaultRenderView("_console");
    }

    private function handleCommand(string $command)
    {
        $toLowerCmd = strtolower($command);
        if(substr($toLowerCmd,0,14) === "new controller")
        {
            return $this->createController(substr($command,15));
        }
        if(substr($toLowerCmd,0,14) === "new middleware")
        {
            return $this->createMiddleWare(substr($command,15));
        }
        if(substr($toLowerCmd,0,9) === "new model")
        {
            return $this->createModel(substr($command,10));
        }
        if(substr($toLowerCmd,0,13) === "new migration")
        {
            return $this->createMigration(substr($command,14));
        }
        if($toLowerCmd === "update migration")
        {
            return Application::$app->db->applyMigrations(true);
        }
        if(substr($toLowerCmd,0,18) === "rollback migration")
        {
            return Application::$app->db->rollback(substr($command,19));
        }
    }

    private function createController(string $name)
    {
        $name = trim($name);

        if (class_exists(Bull::NAMESPACE_HTTP_CONTROLLERS."\\$name"))
        {
            return Application::$app->response->send([
                'message'=>"Controller <strong>'{$name}'</strong> already exist."
            ]);
        }

        if(empty($name))
        {
            return Application::$app->response->send([
                'message'=>"Controller name is not assigned."
            ]);
        }

        $controllerFile = fopen(Application::$rootDir."/http/controllers/${name}.php","wb");
        $content =
            "<?php".PHP_EOL.
            "/**".PHP_EOL.
            "* Class ${name}".PHP_EOL.
            "* @package ".__NAMESPACE__.PHP_EOL.
            "* @author ".self::AUTHOR.PHP_EOL.
             "*/".PHP_EOL.PHP_EOL.PHP_EOL.
            "namespace ".Bull::NAMESPACE_HTTP_CONTROLLERS.";".PHP_EOL.
            "use ". Controller::class.";".PHP_EOL.PHP_EOL.
            "class ${name} extends Controller".PHP_EOL.
            "{".PHP_EOL.PHP_EOL.
            "}";

        fwrite($controllerFile,$content);
        fclose($controllerFile);

        return Application::$app->response->send([
            'message'=>"Controller <strong>'{$name}'</strong> has been created successfully!"
        ]);
    }

    private function createModel(string $name)
    {
        $name = trim($name);

        if (class_exists(Bull::NAMESPACE_DATABASE_MODELS."\\$name"))
        {
            return Application::$app->response->send([
                'message'=>"Model <strong>'${name}'</strong> already exist."
            ]);
        }

        if(empty($name))
        {
            return Application::$app->response->send([
                'message'=>"Model name is not assigned."
            ]);
        }

        $modelFile = fopen(Application::$rootDir."/database/models/${name}.php","wb");

        $content =
            "<?php".PHP_EOL.
            "/**".PHP_EOL.
            "* Class ${name}".PHP_EOL.
            "* @package ".Bull::NAMESPACE_DATABASE_MODELS.PHP_EOL.
            "* @author ".self::AUTHOR.PHP_EOL.
            "*/".PHP_EOL.PHP_EOL.PHP_EOL.
            "namespace ".Bull::NAMESPACE_DATABASE_MODELS.";".PHP_EOL.
            "use ". Bull::NAMESPACE_CORE_DATABASE_MODEL.";".PHP_EOL.PHP_EOL.
            "class ${name} extends Model".PHP_EOL.
            "{".PHP_EOL.PHP_EOL.
            "\tpublic static function tableName(): string".PHP_EOL.
            "\t{".PHP_EOL.
            "\t\t// TODO: Implement tableName() method.".PHP_EOL.
            "\t\treturn 'table';".PHP_EOL.
            "\t}".PHP_EOL.PHP_EOL.
            "}";

        fwrite($modelFile,$content);
        fclose($modelFile);

        return Application::$app->response->send([
            'message'=>"Model <strong>'{$name}'</strong> has been generated successfully!"
        ]);
    }

    private function createMigration(string $name)
    {
        $name = trim($name);

        $files = scandir(Application::$rootDir."/database/migrations");

        $f = array_filter($files,function ($n){
           if ($n !== "." && $n !== "..")
           {
               return $n;
           }
           return null;
        });

        if(empty($f))
        {
            $nextID = "m_0001";
        }
        else
        {
            $nextID = "m_".Bull::formatNumber(substr($files[count($files)-1],2,4)+1,4);
        }

        if(empty($name))
        {
            return Application::$app->response->send([
                'message'=>"Migration name is not assigned."
            ],200);
        }

        $migrationFile = fopen(Application::$rootDir."/database/migrations/${nextID}_${name}.php","wb");

        $content =
            "<?php".PHP_EOL.
            "/**".PHP_EOL.
            "* Class ${name}".PHP_EOL.
            "* @package ".Bull::NAMESPACE_DATABASE_MIGRATIONS.PHP_EOL.
            "* @author ".self::AUTHOR.PHP_EOL.
            "*/".PHP_EOL.PHP_EOL.PHP_EOL.
            "namespace ".Bull::NAMESPACE_DATABASE_MIGRATIONS.";".PHP_EOL.
            "use ". Bull::NAMESPACE_CORE_DATABASE_MIGRATION.";".PHP_EOL.
            "use ". Schema::class.";".PHP_EOL.PHP_EOL.
            "class ${name} implements Migration".PHP_EOL.
            "{".PHP_EOL.PHP_EOL.
            "\tpublic function up()".PHP_EOL.
            "\t{".PHP_EOL.
            "\t\t// TODO: Implement up() method.".PHP_EOL.
            "\t\treturn Schema::create('CREATE TABLE sample_table (id INT AUTO_INCREMENT PRIMARY KEY)');".PHP_EOL.
            "\t}".PHP_EOL.PHP_EOL.
            "\tpublic function down()".PHP_EOL.
            "\t{".PHP_EOL.
            "\t\t// TODO: Implement down() method.".PHP_EOL.
            "\t\treturn Schema::dropIfExists('sample_table');".PHP_EOL.
            "\t}".PHP_EOL.PHP_EOL.
            "}";

        fwrite($migrationFile,$content);
        fclose($migrationFile);

        return Application::$app->response->send([
            'message'=>
            "Migration <strong>'{$name}'</strong> has been
             generated successfully and ready to be applied."
        ]);
    }

    private function createMiddleWare(string $name)
    {
        $name = trim($name);

        if (class_exists(Bull::NAMESPACE_HTTP_MIDDLEWARES."\\$name"))
        {
            return Application::$app->response->send([
                'message'=>"Middleware <strong>'{$name}'</strong> already exist."
            ]);
        }

        if(empty($name))
        {
            return Application::$app->response->send([
                'message'=>"Middleware name is not assigned."
            ]);
        }

        $controllerFile = fopen(Application::$rootDir."/http/middlewares/${name}.php","wb");
        $content =
            "<?php".PHP_EOL.
            "/**".PHP_EOL.
            "* Class ${name}".PHP_EOL.
            "* @package ".Bull::NAMESPACE_HTTP_MIDDLEWARES.PHP_EOL.
            "* @author ".self::AUTHOR.PHP_EOL.
            "*/".PHP_EOL.PHP_EOL.PHP_EOL.
            "namespace ".Bull::NAMESPACE_HTTP_MIDDLEWARES.";".PHP_EOL.
            "use ". Bull::NAMESPACE_CORE_MIDDLEWARES_MIDDLEWARE." as BaseMiddleware;".PHP_EOL.PHP_EOL.
            "class ${name} extends BaseMiddleware".PHP_EOL.
            "{".PHP_EOL.PHP_EOL.
            "\tpublic function execute()".PHP_EOL.
            "\t{".PHP_EOL.PHP_EOL.
            "\t}".PHP_EOL.PHP_EOL.
            "}";

        fwrite($controllerFile,$content);
        fclose($controllerFile);

        return Application::$app->response->send([
            'message'=>"Middleware <strong>'{$name}'</strong> has been created successfully!"
        ]);
    }

}