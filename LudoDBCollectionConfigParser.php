<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 14.01.13
 */
class LudoDBCollectionConfigParser extends LudoDBConfigParser
{
    private $model = null;
    private $tableName;

    public function getTableName()
    {
        if (!isset($this->tableName)) {
            $model = $this->getModel();
            if (isset($model)) $this->tableName = $model->configParser()->getTableName();
        }

        return $this->tableName ;
    }

    /**
     * @return LudoDBModel|null
     */
    public function getModel()
    {
        if (isset($this->config['model'])) {
            // TODO singleton.
            $this->model = $this->getModelInstance();
            $this->model->clearValues();
            return $this->model;
        }
        return null;
    }

    /**
     * @return LudoDBModel
     */
    private function getModelInstance()
    {
        if(!class_exists($this->config['model'])){
            throw new LudoDBException("Class ". $this->config['model']. " does not exists");
        }
        return new $this->config['model'];
    }

    public function getGroupBy(){
        return $this->getProperty('groupBy');
    }

    public function getPK(){
        return $this->getProperty('pk');
    }

    public function getFK(){
        return $this->getProperty('fk');
    }
    public function getChildKey(){
        return $this->getProperty('childKey');
    }

    public function getMerged(){
        return $this->getProperty('merge');
    }

    public function shouldHideForeignKeys(){
        $val = $this->getProperty('hideForeignKeys');
        return isset($val) && $val;
    }
}
