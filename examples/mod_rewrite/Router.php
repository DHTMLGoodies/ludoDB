<?php

require_once(__DIR__ . "/../../autoload.php");

LudoDB::setUser('root');
LudoDB::setPassword('administrator');
LudoDB::setHost('127.0.0.1');
LudoDB::setDb('PHPUnit');

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

$request = $_GET['request'];

if(isset($_POST['request'])){
    $request['data'] = $_POST['data'];
}

$handler = new LudoDBRequestHandler();
echo $handler->handle($request);

