<?php
namespace DerelictIreland\Controllers;

use PDO;
//use Carbon\Carbon;
use League\Plates\Engine;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Controller
{
    protected $tpl;

    protected $dbconn;

    protected $logger;

    public function __construct()
    {

        // Create new Plates instance
        $this->tpl = Engine::create(__DIR__ . '/../../templates');

        $this->tpl->addFolder('errors', __DIR__ . '/../../templates/errors');
        $this->tpl->addFolder('includes', __DIR__ . '/../../templates/includes');

        $servername = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $db = $_ENV['DB_DATABASE'];

        try {
            $this->dbconn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
            // set the PDO error mode to exception
            $this->dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo "Connected successfully";
        } catch (PDOException $e) {
            //echo "Connection failed: " . $e->getMessage();
            //die();
            echo $this->tpl->render('errors::db_cxn_error');
        }

        // create a log channel
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));
    }
}
