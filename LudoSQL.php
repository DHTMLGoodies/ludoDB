<?php
/**
 * User: Alf Magne Kalleland
 * Date: 22.12.12
 * Time: 00:19
 */
class LudoSQL
{
    private $config;
    private $constructorValues;
    /**
     * @var LudoDBObject
     */
    private $configParser;

    public function __construct(LudoDBObject $obj){
        $this->configParser = $obj->configParser();
        $this->config = $obj->getConfig();
        $this->constructorValues = $obj->getConstructorValues();
        $this->validate();
    }

    private function validate(){
        if(isset($this->constructorValues) && !is_array($this->constructorValues))$this->constructorValues = array($this->constructorValues);
    }

    public function getSql(){
        return "select ". $this->getColumns(). " from ". $this->getTables().$this->getJoins().$this->getOrderBy();
    }

    private function getColumns(){
        $ret = array();
        if($this->configParser->hasColumns()){
            $ret = $this->getColumnsForSql();
        }
        if(!$ret){
            $ret = $this->configParser->getTableName().".*";
        }
        $ret.=$this->getColumnsFromJoins();
        return $ret;
    }

    private function getColumnsForSql(){
        if(isset($this->config['columns'][0])){
            return $this->getColumnsForCollectionSQL();
        }
        $ret = array();
        $cols = $this->configParser->getColumns();
        foreach($cols as $col => $value){
            if(! is_array($value)){
                $ret[]=$this->configParser->getTableName().".". $col;
            }
        }
        return implode(",", $ret);
    }

    private function getColumnsForCollectionSQL(){
        return $this->configParser->getTableName()."." . implode(",". $this->configParser->getTableName().".",$this->configParser->getColumns());
    }

    private function getColumnsFromJoins(){
        $joins = $this->configParser->getColumnsFromJoins();
        if(count($joins)){
            return ",". implode(",", $joins);
        }
        return '';
    }

    private function getTables(){
        return implode(",", array_merge(array($this->configParser->getTableName()),$this->configParser->getTableNamesFromJoins()));
    }

    private function getJoins(){
        $ret = $this->configParser->getJoinsForSQL();
        $constructorParams = $this->configParser->getConstructorParams();
        if(isset($constructorParams)){
            for($i=0,$count=count($this->constructorValues);$i<$count; $i++){
                $ret[] = $this->getTableAndColumn($constructorParams[$i])."='". mysql_real_escape_string($this->constructorValues[$i])."'";
            }

        }
        if(count($ret)){
            return " where ". implode(" and ", $ret);
        }
        return '';
    }

    private function getTableAndColumn($column){
        return strstr($column, ".") ? $column : $this->configParser->getTableName().".".$column;
    }

    private function getOrderBy(){
        $orderBy = $this->configParser->getOrderBy();
        return isset($orderBy) ? ' order by ' . $orderBy : '';
    }

    public function getCreateTableSql(){
        $sql = "create table " . $this->configParser->getTableName() . "(";
        $columns = array();
        $configColumns = $this->configParser->getColumns();
        foreach ($configColumns as $name => $type) {
            if (is_string($type)) {
                $columns[] = $name . " " . $type;
            }
        }
        $sql .= implode(",", $columns) . ")";
        return $sql;
    }

    public function getDeleteSQL(){
        $sql = "delete from ". $this->configParser->getTableName(). " where ";
        $where = array();
        $configParams = $this->configParser->getConstructorParams();
        for($i=0,$count = count($this->constructorValues);$i<$count;$i++){
            $val = $this->constructorValues[$i];
            if(is_string($val)){
                $val = "'".$val."'";
            }
            $where[] = $configParams[$i]."=". $val;
        }
        $sql.=implode(" and ", $where);
        return $sql;
    }
}
