<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13
 * Time: 15:16
 * To change this template use File | Settings | File Templates.
 */
class Capitals extends LudoDBCollection implements LudoDBService
{
    protected $JSONConfig = true;
    protected $caching = true;

    public function __construct($fromZip, $toZip){
        parent::__construct($fromZip, $toZip);
    }

    public function areValidServiceArguments($service, $arguments){
        return count($arguments) === 2 && is_numeric($arguments[0]) && is_numeric($arguments[1]);
    }

    public static function getValidServices(){
        return array('read','delete','save');
    }
}
