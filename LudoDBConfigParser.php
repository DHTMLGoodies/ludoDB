<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */
class LudoDBConfigParser
{
    private $config;
    private static $columnMappingCache = array();
    private $customConstructorParams;
    public function __construct(LudoDBObject $obj)
    {
        $this->buildConfig($obj);
    }

    /**
     * @param LudoDBObject $obj
     */
    private function buildConfig($obj){
        $this->config = $this->getValidConfig($obj->getConfig());
        $parent = $this->getExtends();
        if(isset($parent)){
            $this->config = $this->getMergedConfigs($parent->configParser()->getConfig(), $this->config);
        }
    }

    public function setConstructorParams($params){
        if(!is_array($params))$params = array($params);
        $this->customConstructorParams = $params;
    }

    private function getMergedConfigs($config1, $config2)
    {
        if (!is_array($config1) or !is_array($config2)) { return $config2; }
        foreach ($config2 AS $sKey2 => $sValue2)
        {
            $config1[$sKey2] = $this->getMergedConfigs(@$config1[$sKey2], $sValue2);
        }
        return $config1;
    }
    
    public function getConfig(){
        return $this->config;
    }


    private function getValidConfig($config)
    {
        if (!isset($config['constructorParams']) && isset($config['idField'])) {
            $config['constructorParams'] = array($config['idField']);
        }
        if (isset($config['constructorParams']) && !is_array($config['constructorParams'])) {
            $config['constructorParams'] = array($config['constructorParams']);
        }
        return $config;
    }

    public function getTableName()
    {
        return $this->config['table'];
    }

    public function getConstructorParams()
    {
        if(isset($this->customConstructorParams))return $this->customConstructorParams;
        return isset($this->config['constructorParams']) ? $this->config['constructorParams'] : null;
    }

    public function externalClassNameFor($column)
    {
        return $this->getColumnProperty($column, 'class');
    }

    private function getColumnProperty($column, $property)
    {
        if ($ret = $this->getExternalClassProperty($column, $property)) {
            return $ret;
        }
        if (isset($this->config['columns'][$column])) {
            return is_array($this->config['columns'][$column])
                && isset($this->config['columns'][$column][$property]) ?
                $this->config['columns'][$column][$property] : null;
        }
        return null;
    }

    private function getExternalClassProperty($column, $property)
    {
        if (isset($this->config['classes']) && isset($this->config['classes'][$column])) {
            $cl = $this->config['classes'];
            return isset($cl[$column]) && isset($cl[$column][$property]) ? $cl[$column][$property] : null;
        }
        return null;
    }

    public function isExternalColumn($column)
    {

        if (isset($this->config['columns'][$column]) && is_array($this->config['columns'][$column])) {
            if (isset($this->config['columns'][$column]['db'])) return false;
            return true;
        }
        return false;
    }

    public function getIdField()
    {
        return isset($this->config['idField']) ? $this->config['idField'] : 'id';
    }

    public function isIdAutoIncremented()
    {
        return strstr($this->getDbDefinition($this->getIdField()), 'auto_increment') ? true : false;
    }

    private function getDbDefinition($column){
        $col = $this->config['columns'][$column];
        return is_array($col) ? $col['db'] : $col;
    }

    public function getSetMethod($column)
    {
        return $this->getColumnProperty($column, 'set');
    }

    public function getGetMethod($column)
    {
        $method = $this->getColumnProperty($column, 'get');
        return isset($method) ? $method : 'getValues';
    }

    public function foreignKeyFor($column)
    {
        return $this->getColumnProperty($column, 'fk');
    }

    public function getIndexes()
    {
        return $this->getProperty('indexes');
    }

    public function getDefaultData()
    {
        return $this->getProperty('data');
    }

    public function getJoins()
    {
        return $this->getProperty('join');
    }

    public function getMyColumnsForSQL(){
        $columns = $this->getColumns();
        $ret = array();
        foreach($columns as $col => $value){
            if(!$this->isExternalColumn($col)){
                $ret[] = $this->getTableName().".".$col;
            }
        }
        return $ret;

    }

    public function getJoinsForSQL()
    {
        $joins = $this->getJoins();
        $ret = array();
        if (isset($joins)) {
            foreach ($joins as $join) {
                $ret[] = $this->getTableName() . "." . $join['fk'] . "=" . $join['table'] . "." . $join['pk'];
            }
        }
        return $ret;
    }

    public function getColumnsFromJoins()
    {
        $ret = array();
        $joins = $this->getJoins();
        if (isset($joins)) {
            foreach ($joins as $join) {
                foreach ($join['columns'] as $col) {
                    $ret[] = $join['table'] . "." . $col;
                }
            }
        }
        return $ret;
    }

    public function getColumns()
    {
        return $this->getProperty('columns');
    }

    private function getColumn($column){
        return isset($this->config['columns'][$column]) ? $this->config['columns'][$column] : null;
    }

    public function getOrderBy()
    {
        return $this->getProperty('orderBy');
    }

    public function hasColumns()
    {
        $cols = $this->getColumns();
        return isset($cols) && is_array($cols) && count($cols) > 0;
    }

    public function getTableNamesFromJoins()
    {
        $ret = array();
        $joins = $this->getJoins();
        if (isset($joins)) {
            foreach ($joins as $join) {
                $ret[] = $join['table'];
            }
        }
        return $ret;
    }

    private function getProperty($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    private function hasColumn($columnName)
    {
        return isset($this->config['columns'][$columnName]);
    }

    public function getColumnByMethod($methodName)
    {
        if (!$this->hasColumns()) return null;
        $col = $this->getFromMappingCache($methodName);
        if (!isset($col)) {
            $col = substr($methodName, 3);
            if ($this->hasColumn($col)) return $this->saveInMappingCache($methodName, $col);
            $col = lcfirst($col);
            if ($this->hasColumn($col)) return $this->saveInMappingCache($methodName, $col);
            $col = strtolower(preg_replace("/([A-Z])/s", "_$1", $col));
            if ($this->hasColumn($col)) return $this->saveInMappingCache($methodName, $col);
        }

        return $col;
    }

    private function saveInMappingCache($methodName, $col){
        $t = $this->getTableName();
        if (!isset(self::$columnMappingCache[$t])) {
            self::$columnMappingCache[$t] = array();
        }
        self::$columnMappingCache[$t][$methodName] = $col;
        return $col;
    }

    private function getFromMappingCache($methodName)
    {
        $t = $this->getTableName();
        if (!isset(self::$columnMappingCache[$t])) {
            self::$columnMappingCache[$t] = array();
        }
        return isset(self::$columnMappingCache[$t][$methodName]) ? self::$columnMappingCache[$t][$methodName] : null;
    }

    public function canWriteTo($column){
        $column = $this->getColumn($column);
        if(isset($column) && isset($column['access'])){
            return strstr($column['access'], "w") ? true : false;
        }
        return false;
    }
    public function canReadFrom($column){
        $column = $this->getColumn($column);
        if(isset($column) && isset($column['access'])){
            return strstr($column['access'], "r") ? true : false;
        }
        return false;
    }

    /**
     * @return LudoDBObject
     */
    private function getExtends(){
        $className = $this->getProperty('extends');
        if(isset($className)){
            return new $className;

        }
        return null;
    }

    public function getColumnType($column){
        if(isset($this->config['columns'][$column])){
            $col = $this->config['columns'][$column];
            return is_string($col) ? $col : $col['db'];
        }
        return null;
    }

    public function getTypesForPreparedSQL($columns){
        $ret = array();
        foreach($columns as $column){
            $ret[$column] = $this->getTypeForPreparedSQL($column);
        }
        return $ret;
    }


    public function getTypeForPreparedSQL($column){
        $type = $this->getColumnType($column);
        if(isset($type)){
            $tokens = preg_split("/[^a-z]/si", $type);
            $type = strtolower($tokens[0]);

            switch($type){
                case 'varchar':
                case 'char':
                case 'text':
                case 'mediumtext':
                    return 's';
                case 'shortint':
                case 'mediumint':
                case 'longint':
                case 'int':
                    return 'i';
                case 'double':
                case 'float':
                    return 'd';
                case 'blob':
                    return 'b';
                default:
                    return 's';
            }
        }
        return null;
    }

    public function canBePopulatedBy($column){
        if($column == $this->getIdField())return true;
        $col = $this->getColumn($column);
        if(isset($this->config['constructorParams']) && in_array($column, $this->config['constructorParams'])){
            return true;
        }
        return is_array($col) && isset($col['canConstructBy']) ? $col['canConstructBy'] : false;
    }
}
