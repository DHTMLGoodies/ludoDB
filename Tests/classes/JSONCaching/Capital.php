<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13


 */
class Capital extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true;

    public function __construct($id = null){
        parent::__construct($id);
    }

    public function getValidServices(){
        return array('read','delete','save');
    }

    public function setName($name){
        $this->setValue('name', $name);
    }

    public function getName(){
        return $this->getValue('name');
    }

    public function clearCache(){
        LudoDBCache::clearByClass('Capitals');
        parent::clearCache();
    }

    public function validateArguments($service, $arguments){
        return empty($arguments) || count($arguments) === 1 && is_numeric($arguments[0]) ? true: false;
    }

    public function validateServiceData($service, $data){
        return true;
    }

    public function shouldCache($service){
        return true;
    }
}
