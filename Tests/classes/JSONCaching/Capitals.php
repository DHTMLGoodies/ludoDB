<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13


 */
class Capitals extends LudoDBCollection implements LudoDBService
{
    protected $JSONConfig = true;

    public function __construct($fromZip, $toZip){
        parent::__construct($fromZip, $toZip);
    }

    public function validateArguments($service, $arguments){
        return count($arguments) === 2 && is_numeric($arguments[0]) && is_numeric($arguments[1]);
    }

    public function validateServiceData($service, $data){
        return true;
    }

    public function getValidServices(){
        return array('read','delete','save');
    }

    public function shouldCache($service){
        return $service === "read";
    }
}
