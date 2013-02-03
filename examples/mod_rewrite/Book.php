<?php
/**
 * Simple model for a book.
 * User: xAlf Magne Kalleland
 * Date: 03.02.13
 * Time: 13:23
 */
class Book extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true; // Config on JSONConfig/Book.json
    public static $validServices = array('read','save','delete');

    public function areValidServiceArguments($service, $arguments){
        switch($service){
            case 'delete':
                return count($arguments) === 1 && is_numeric($arguments[0]);
            default:
                return count($arguments) === 0 || is_numeric($arguments[0]) && count($arguments) === 1;
        }

    }
}
