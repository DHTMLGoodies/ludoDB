<?php
/**
 * Template for a router, i.e. entry point for all requests sent by Ajax
 * from GUI.
 */
require_once(__DIR__ . "/autoload.php");
error_reporting(E_ALL);
ini_set('display_errors','on');

date_default_timezone_set("Europe/Berlin");
header("Content-type: application/json");

if (!file_exists("connect.php")) {
    die("You need to create connect.php containing the database connection details, example:
        LudoDB::setUser('root');
        LudoDB::setPassword('administrator');
        LudoDB::setHost('127.0.0.1');
        LudoDB::setDb('PHPUnit');
        LudoDB::enableLogging();");
}

require_once("connect.php");

LudoDB::enableLogging();

$request = isset($_GET['request']) ? $_GET['request'] : $_POST['request'];
$requestData = isset($_POST['data']) ? $_POST['data'] : null;


$handler = new LudoDBRequestHandler();
echo $handler->handle($request, $requestData);