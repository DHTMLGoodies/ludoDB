<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13


 */
class Capitals extends LudoDBCollection implements LudoDBService
{
    protected $JSONConfig = true;
    protected $caching = true;

    public function __construct($fromZip, $toZip){
        parent::__construct($fromZip, $toZip);
    }

    public function validateService($service, $arguments){
        return count($arguments) === 2 && is_numeric($arguments[0]) && is_numeric($arguments[1]);
    }

    public static function getValidServices(){
        return array('read','delete','save');
    }
}
