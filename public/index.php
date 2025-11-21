<?php
namespace DerelictIreland;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Require composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Define root path
defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
//defined('ROOT') ?: define('ROOT', dirname(__DIR__) . DS);

//defined('ROOT') ?: define('ROOT', dirname(__DIR__.'/../') . DS);

// Load .env file
if (file_exists(__DIR__.DS.'..'.DS.'.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__.DS.'..');
    $dotenv->load();
// $dotenv = new Dotenv\Dotenv(ROOT);
    // $dotenv->load();
} else {
    echo dirname(__DIR__ . '/../../');
    exit;
}

use Bramus\Router\Router;

// Create a Router
$router = new Router();

//$settings = require __DIR__ . '/../settings.php';

$router->setNamespace('\DerelictIreland\Controllers');

// Load our custom routes
require_once '../routes.php';

// on your bike !
$router->run();
