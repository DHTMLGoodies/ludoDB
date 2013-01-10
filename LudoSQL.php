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
    private $obj;

    public function __construct(LudoDBObject $obj){
        $this->obj = $obj;
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
        if($this->hasColumns()){
            $ret = $this->getColumnsForSql();
        }
        if(!$ret){
            $ret = $this->obj->configParser()->getTableName().".*";
        }
        $ret.=$this->getColumnsFromJoins();
        return $ret;
    }

    private function getColumnsForSql(){
        if(isset($this->config['columns'][0])){
            return $this->obj->configParser()->getTableName()."." . implode(",". $this->obj->configParser()->getTableName().".",$this->config['columns']);
        }
        $ret = array();
        $cols = $this->config['columns'];
        foreach($cols as $col => $value){
            if(! is_array($value)){
                $ret[]=$this->obj->configParser()->getTableName().".". $col;
            }
        }
        return implode(",", $ret);
    }

    private function getColumnsFromJoins(){
        $ret = array();
        if(isset($this->config['join'])){
            foreach($this->config['join'] as $join){
                $columns = array();
                foreach($join['columns'] as $col){
                    $columns[] = $join['table'].".".$col;
                }
                $ret[] = implode(",", $columns);
            }
        }
        if(count($ret)){
            return ",". implode(",", $ret);
        }
        return '';
    }

    private function hasColumns(){
        return isset($this->config['columns']) && count($this->config['columns']) > 0;
    }

    private function getTables(){
        $ret = array($this->obj->configParser()->getTableName());
        if(isset($this->config['join'])){
            foreach($this->config['join'] as $join){
                $ret[] = $join['table'];
            }
        }
        return implode(",", $ret);
    }

    private function getJoins(){
        $ret = array();
        $joins = $this->obj->configParser()->getJoins();
        if(isset($joins)){
            foreach($joins as $join){
                $ret[] = $this->obj->configParser()->getTableName().".".$join['fk']."=".$join['table'].".".$join['pk'];
            }
        }
        $constructorParams = $this->obj->configParser()->getConstructorParams();
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
        return strstr($column, ".") ? $column : $this->obj->configParser()->getTableName().".".$column;
    }

    private function getOrderBy(){
        $orderBy = $this->obj->configParser()->getOrderBy();
        return isset($orderBy) ? ' order by ' . $orderBy : '';
    }

    public function getCreateTableSql(){
        $sql = "create table " . $this->obj->configParser()->getTableName() . "(";
        $columns = array();
        $configColumns = $this->obj->configParser()->getColumns();
        foreach ($configColumns as $name => $type) {
            if (is_string($type)) {
                $columns[] = $name . " " . $type;
            }
        }
        $sql .= implode(",", $columns) . ")";
        return $sql;
    }

    public function getDeleteSQL(){
        $sql = "delete from ". $this->obj->configParser()->getTableName(). " where ";
        $where = array();
        $configParams = $this->obj->configParser()->getConstructorParams();
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

    public function log($sql){
        $fh = fopen("sql.txt","a+");
        fwrite($fh, $sql."\n");
        fclose($fh);
    }
}
