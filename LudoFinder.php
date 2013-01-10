<?php
/**
 * DB Record finder class
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 16:07
 */
class LudoFinder
{
    private $sql;
    private $applyTo;
    private $db;

    public function __construct(LudoDbTable $applyTo){
        $this->applyTo = $applyTo;
        $this->sql = array();
        $this->db = new LudoDB();
        return $this;
    }

    /**
     * Add where clause
     * @method where
     * @param $column
     * @param $value
     * @return LudoFinder
     */
    public function where($column, $value){
        if($this->applyTo->hasColumn($column)){
            $this->sql[] = $column .= "='".$value."'";
        }
        return $this;
    }

    /**
     * Populate LudoDbTable object and return it
     * @method find
     * @return LudoDbTable
     */
    public function find(){
        $id = $this->db->getValue($this->getCompiledSql());
        $this->applyTo->populate($id);
        return $this->applyTo;
    }

    private function getCompiledSql(){
        $ret = "select ". $this->applyTo->configParser()->getIdField()." as id from ". $this->applyTo->configParser()->getTableName();
        if($this->sql)$ret.=" where ". implode(" and ", $this->sql);
        return $ret;
    }
}
