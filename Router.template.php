<?php
/**
 * Template for a router, i.e. entry point for all requests sent by Ajax
 * from GUI.
 */
require_once(__DIR__."/autoload.php");

date_default_timezone_set("Europe/Berlin");
header("Content-type: application/json");

LudoDB::setUser('root');
LudoDB::setPassword('administrator');
LudoDB::setHost('127.0.0.1');
LudoDB::setDb('PHPUnit');

$request = array('request' => isset($_GET['request']) ? $_GET['request'] : $_POST['request']);

if(isset($_POST['request'])){
    $request['data'] = isset($_POST['request']['data']) ? $_POST['request']['data'] : $_POST['request'];
}

if(isset($_POST['arguments'])){
    $request['arguments'] = $_POST['arguments'];
}

$handler = new LudoDBRequestHandler();
echo $handler->handle($request);