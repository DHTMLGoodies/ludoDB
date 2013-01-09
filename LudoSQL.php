<?php
/**
 * User: Alf Magne Kalleland
 * Date: 22.12.12
 * Time: 00:19
 */
class LudoSQL
{
    private $config;
    private $queryValues;

    public function __construct($config, $queryValues = null){
        $this->config = $config;
        $this->queryValues = $queryValues;
        $this->validate();
    }

    private function validate(){
        if(isset($this->queryValues) && !is_array($this->queryValues))$this->queryValues = array($this->queryValues);
        if(isset($this->config['queryFields']) && !is_array($this->config['queryFields']))$this->config['queryFields'] = array($this->config['queryFields']);
    }

    public function getSql(){
        $this->log("select ". $this->getColumns(). " from ". $this->getTables().$this->getJoins().$this->getOrderBy());
        return "select ". $this->getColumns(). " from ". $this->getTables().$this->getJoins().$this->getOrderBy();
    }

    private function getColumns(){
        $ret = array();
        if($this->hasColumns()){
            $ret = $this->getColumnsForSql();
        }
        if(!$ret){
            $ret = $this->config['table'].".*";
        }
        $ret.=$this->getColumnsFromJoins();
        return $ret;
    }

    private function getColumnsForSql(){
        if(isset($this->config['columns'][0])){
            return $this->config['table']."." . implode(",". $this->config['table'].".",$this->config['columns']);
        }
        $ret = array();
        $cols = $this->config['columns'];
        foreach($cols as $col => $value){
            if(! is_array($value)){
                $ret[]=$this->config['table'].".". $col;
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
        $ret = array($this->config['table']);
        if(isset($this->config['join'])){
            foreach($this->config['join'] as $join){
                $ret[] = $join['table'];
            }
        }
        return implode(",", $ret);
    }

    private function getJoins(){
        $ret = array();
        if(isset($this->config['join'])){
            foreach($this->config['join'] as $join){
                $ret[] = $this->config['table'].".".$join['fk']."=".$join['table'].".".$join['pk'];
            }
        }
        if(isset($this->config['queryFields'])){
            for($i=0,$count=count($this->queryValues);$i<$count; $i++){
                $ret[] = $this->getTableAndColumn($this->config['queryFields'][$i])."='". mysql_real_escape_string($this->queryValues[$i])."'";
            }

        }
        if(count($ret)){
            return " where ". implode(" and ", $ret);
        }
        return '';
    }

    private function getTableAndColumn($column){
        return strstr($column, ".") ? $column : $this->config['table'].".".$column;
    }

    private function getOrderBy(){
        return isset($this->config['orderBy']) ? ' order by ' . $this->config['orderBy'] : '';
    }

    public function getCreateTableSql(){
        $sql = "create table " . $this->config['table'] . "(";
        $columns = array();
        foreach ($this->config['columns'] as $name => $type) {
            if (is_string($type)) {
                $columns[] = $name . " " . $type;
            }
        }
        $sql .= implode(",", $columns) . ")";
        return $sql;
    }

    public function log($sql){
        $fh = fopen("sql.txt","a+");
        fwrite($fh, $sql."\n");
        fclose($fh);
    }
}
