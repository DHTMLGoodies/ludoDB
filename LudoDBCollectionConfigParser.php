<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 14.01.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Config parser for LudoDBCollection
 * @package LudoDB
 */
class LudoDBCollectionConfigParser extends LudoDBConfigParser
{
    /**
     * Internal reference to LudoDBCollection
     * @var LudoDBCollection
     */
    private $model = null;

    /**
     * Name of table name for the LudoDBCollection handled by the parser
     * @var string
     */
    private $tableName;


    /**
     * Return table name
     * @return string
     */
    public function getTableName()
    {
        if (!isset($this->tableName)) {
            $model = $this->getModel();
            if (isset($model)) $this->tableName = $model->configParser()->getTableName();
        }

        return $this->tableName ;
    }

    /**
     * Return LudoDBCollection handled by the parser.
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
     * Return LudoDBCollection instance
     * @return LudoDBModel
     */
    private function getModelInstance()
    {
        if(!class_exists($this->config['model'])){
            throw new LudoDBException("Class ". $this->config['model']. " does not exists");
        }
        return new $this->config['model'];
    }

    /**
     * Return group by config property.
     * @return string|null
     */
    public function getGroupBy(){
        return $this->getProperty('groupBy');
    }

    /**
     * Return primary key config property.
     * @return string|null
     */
    public function getPK(){
        return $this->getProperty('pk');
    }
    /**
     * Return foreign key config property.
     * @return string|null
     */
    public function getFK(){
        return $this->getProperty('fk');
    }

    /**
     * Return childKey config property.
     * @return string|null
     */
    public function getChildKey(){
        return $this->getProperty('childKey');
    }
    /**
     * Return merge config property.
     * @return array|null
     */
    public function getMerged(){
        return $this->getProperty('merge');
    }
    /**
     * Returns true when foreign key column should be hidden from returned rows, i.e.
     * the hideForeignKeys config property.
     * @return bool
     */
    public function shouldHideForeignKeys(){
        $val = $this->getProperty('hideForeignKeys');
        return isset($val) && $val;
    }
}
