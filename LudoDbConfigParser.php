<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */
class LudoDbConfigParser
{
    private $config;
    private $obj;

    public function __construct(LudoDBObject $obj){
        $this->config = $this->getValidConfig($obj->getConfig());
    }



    private function getValidConfig($config){
        if(!isset($config['constructorParams']) && isset($config['idField'])){
            $config['constructorParams'] = array($config['idField']);
        }
        if(isset($config['constructorParams']) && !is_array($config['constructorParams'])){
            $config['constructorParams'] = array($config['constructorParams']);
        }
        return $config;
    }

    public function getTableName(){
        return $this->config['table'];
    }

    public function getConstructorParams(){
        return isset($this->config['constructorParams']) ? $this->config['constructorParams'] : null;
    }

    public function externalClassNameFor($column){
        return $this->getColumnProperty($column, 'class');
    }

    private function getColumnProperty($column, $property){
        if($ret = $this->getExternalClassProperty($column,$property)){
            return $ret;
        }
        if(isset($this->config['columns'][$column])){
            return is_array($this->config['columns'][$column])
                && isset($this->config['columns'][$column][$property]) ?
                $this->config['columns'][$column][$property] : null;
        }
        return null;
    }

    private function getExternalClassProperty($column, $property){
        if (isset($this->config['classes']) && isset($this->config['classes'][$column])) {
            $cl = $this->config['classes'];
            return isset($cl[$column]) && isset($cl[$column][$property]) ? $cl[$column][$property] : null;
        }
        return null;
    }

    public function isExternalColumn($column){
       return isset($this->config['columns'][$column]) && is_array($this->config['columns'][$column]);
    }

    public function getIdField(){
        return isset($this->config['idField']) ? $this->config['idField'] : 'id';
    }

    public function idIsAutoIncremented(){
        return strstr($this->config['columns'][$this->getIdField()], 'auto_increment') ? true : false;
    }

    public function getSetMethod($column){
        return $this->getColumnProperty($column, 'set');
    }

    public function getGetMethod($column){
        $method = $this->getColumnProperty($column, 'get');
        return isset($method) ? $method : 'getValues';
    }

    public function foreignKeyFor($column){
        return $this->getColumnProperty($column, 'fk');
    }

    public function getIndexes(){
        return $this->getProperty('indexes');
    }

    public function getDefaultData(){
        return $this->getProperty('data');
    }

    public function getJoins(){
        return $this->getProperty('join');
    }

    public function getJoinsForSQL(){
        $joins = $this->getJoins();
        $ret = array();
        if(isset($joins)){
            foreach($joins as $join){
                $ret[] = $this->getTableName().".".$join['fk']."=". $join['table'].".".$join['pk'];
            }
        }
        return $ret;
    }

    public function getColumnsFromJoins(){
        $ret = array();
        $joins = $this->getJoins();
        if(isset($joins)){
            foreach($joins as $join){
                foreach($join['columns'] as $col){
                    $ret[] = $join['table'].".".$col;
                }
            }
        }
        return $ret;
    }

    public function getColumns(){
        return $this->getProperty('columns');
    }

    public function getOrderBy(){
        return $this->getProperty('orderBy');
    }

    public function hasColumns(){
        $cols = $this->getColumns();
        return isset($cols) && is_array($cols) && count($cols) >0;
    }

    public function getTableNamesFromJoins(){
        $ret = array();
        $joins = $this->getJoins();
        if(isset($joins)){
            foreach($joins as $join){
                $ret[] = $join['table'];
            }
        }
        return $ret;
    }

    private function getProperty($key){
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }
}
