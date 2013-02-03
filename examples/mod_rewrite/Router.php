<?php

require_once(__DIR__ . "/../../autoload.php");
date_default_timezone_set('Europe/Berlin');
error_reporting(E_ALL);
ini_set('display_errors','on');


LudoDB::setUser('root');
LudoDB::setPassword('administrator');
LudoDB::setHost('127.0.0.1');
LudoDB::setDb('PHPUnit');

/**
 * Auto create Book table if it doesn't exists. This is just for this demo/sample
 */
$book = new Book();
if(!$book->exists())$book->createTable();



LudoDB::enableLogging();

$request = $_GET['request'];

if(isset($_POST['request'])){
    $request['data'] = $_POST['data'];
}

$handler = new LudoDBRequestHandler();
echo $handler->handle($request);

