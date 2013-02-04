<?php

require_once(__DIR__ . "/../../autoload.php");

header("Content-type: application/json");

LudoDB::setUser('root');
LudoDB::setPassword('administrator');
LudoDB::setHost('127.0.0.1');
LudoDB::setDb('PHPUnit');

ini_set('display_errors','on');
/**
 * Auto create database tables. This is just for this demo/sample
 */
$book = new Book();
if(!$book->exists())$book->createTable();
$bookAuthor = new BookAuthor();
if(!$bookAuthor->exists())$bookAuthor->createTable();
$author = new Author();
if(!$author->exists())$author->createTable();

LudoDB::enableLogging();

$request = array(
    'request' => $_GET['request']
);

if(isset($_POST)){
    $request['data'] = $_POST;
}

$handler = new LudoDBRequestHandler();
echo $handler->handle($request);

