<?php

require_once(__DIR__ . "/../../autoload.php");

header("Content-type: application/json");

LudoDB::setUser('root');
LudoDB::setPassword('administrator');
LudoDB::setHost('127.0.0.1');
LudoDB::setDb('PHPUnit');

/*
// Drop tables.
$bookAuthor = new BookAuthor();
$bookAuthor->drop()->yesImSure();
$book = new Book();
$book->drop()->yesImSure();
$bookAuthor = new BookAuthor();
$bookAuthor->drop()->yesImSure();
*/

/**
 * Auto create database tables. This is just for this demo/sample
 */
$book = new Book();
if(!$book->exists())$book->createTable();

$author = new Author();
if(!$author->exists())$author->createTable();

$bookAuthor = new BookAuthor();
if(!$bookAuthor->exists())$bookAuthor->createTable();


LudoDB::enableLogging();

$request = $_GET['request'];
$data = array();


foreach($_POST as $key=>$value){
    $data[$key] = $value;
}

$handler = new LudoDBRequestHandler();
echo $handler->handle($request, $data);

