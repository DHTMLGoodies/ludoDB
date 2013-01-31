<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13
 * Time: 15:55
 * To change this template use File | Settings | File Templates.
 */
class Capital extends LudoDBModel
{
    protected $JSONConfig = true;
    protected $caching = true;

    public function __construct($id = null){
        parent::__construct($id);
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
}
