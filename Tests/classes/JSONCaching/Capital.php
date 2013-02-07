<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13


 */
class Capital extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true;
    protected $caching = true;

    public function __construct($id = null){
        parent::__construct($id);
    }

    public static function getValidServices(){
        return array('read','delete','save');
    }

    public function setName($name){
        $this->setValue('name', $name);
    }

    public function getName(){
        return $this->getValue('name');
    }

    public function clearCache(){
        LudoDBCache::clearCacheByClass('Capitals');
        parent::clearCache();
    }

    public function areValidServiceArguments($service, $arguments){
        return empty($arguments) || count($arguments) === 1 && is_numeric($arguments[0]) ? true: false;
    }
}
