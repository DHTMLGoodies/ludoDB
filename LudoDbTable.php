<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 03.11.12
 * Time: 01:45
 * To change this template use File | Settings | File Templates.
 */
class LudoDbTable
{
    protected $db;
    protected $tableName;
    protected $idField = 'id';
    protected $config = array(
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
        )
    );

    /**
     * Array of search fields. if non-numeric value is sent to constructor, this object can be
     * populated by using search fields
     * @var array search fields
     */
    protected $searchFields = array();

    /**
     * Array of search fields. if non-numeric value is sent to constructor, this object can
     * be populated by using like queries on these fields
     * @var array likeFields
     *
     */
    protected $likeFields = array();

    private $id;
    private $data = array();
    private $updates;
    private $compiledSql = null;

    public function __construct($ref = null)
    {
        $this->db = new LudoDb();
        if(isset($ref))$this->populate($ref);

    }

    private function populate($ref)
    {
        if(!isset($this->compiledSql)){
            $this->compileSql();
        }
        if (is_numeric($ref)) {
            $data = $this->db->one($this->compiledSql. " and t." . $this->idField . "='" . $ref . "'");

        } else {
            if (count($this->searchFields)) {
                $data = $this->getBySearchFields($ref);
            }
            if (!isset($data) && count($this->likeFields)) {
                $data = $this->getByLikeFields($ref);
            }
        }
        if (isset($data)) {
            $this->populateWith($data);
        }

    }

    private function compileSql(){
        $sql = "select t.*";
        $joins = $this->getSQLJoin();
        if(count($joins['columns'])){
            $sql.=','.implode(",", $joins['columns']);
        }
        $sql.=" from ". $this->tableName. " t";
        if(count($joins['from'])){
            $sql.=",". implode($joins['from'], ",");
        }
        $sql.=" where 1=1";
        if(count($joins['where'])){
            $sql.=" and ". implode($joins['where'],' and ');
        }
        $this->compiledSql = $sql;
    }


    private function getSQLJoin(){
        $ret = array(
            'columns' => array(),
            'from' => array(),
            'where' => array()
        );
        if(!isset($this->config['join']))return $ret;
        $joins = $this->config['join'];
        $i=1;
        foreach($joins as $join){
            $ret['columns'][] = 't'.$i. ".".implode($join['columns'], ",t".$i.".");
            $ret['from'][]  = $join['table'] . " t". $i;
            $ret['where'][] = 't.'.$join['fk']."=t".$i.".".$join['pk'];
            $i++;
        }
        return $ret;
    }

    private function getBySearchFields($ref)
    {
        foreach ($this->searchFields as $field) {
            $data = $this->db->one($this->compiledSql . " and t." . $field . " = '" . $ref . "'");
            if (isset($data)) return $data;
        }
        return null;
    }

    private function getByLikeFields($ref)
    {
        foreach ($this->likeFields as $field) {
            $data = $this->db->one($this->compiledSql . " and t." . $field . " like '%" . $ref . "%'");
            if (isset($data)) return $data;
        }
        return null;
    }

    private function populateWith($data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    protected function getColumnValue($column)
    {
        if(isset($this->updates) && isset($this->updates[$column])){
            return $this->updates[$column];
        }
        return isset($this->data[$column]) ? $this->data[$column] : null;
    }

    protected function setValue($column, $value)
    {
        if(!isset($this->updates))$this->updates = array();
        if(is_string($value))$value = mysql_real_escape_string($value);
        $this->updates[$column] = $value;
    }

    public function commit()
    {
        if (!isset($this->updates)) return;
        if($this->getId()){
            $this->update();
        }else{
            $this->insert();
        }
        foreach ($this->updates as $key => $value) {
            $this->data[$key] = $value;
        }
        $this->updates = null;
    }

    private function update(){
        $sql = "update " . $this->tableName . " set " . $this->getUpdatesForSql() . " where " . $this->idField . " = '" . $this->getId() . "'";
        $this->db->query($sql);

    }

    private function insert(){
        $sql  ="insert into ". $this->tableName;
        $sql.="(". implode(",", array_keys($this->updates)). ")";
        $sql.="values('". implode("','", array_values($this->updates))."')";
        $this->db->query($sql);

        $this->id = mysql_insert_id();

    }

    public function rollback()
    {
        $this->updates = null;
    }

    public function getUpdates(){
        return $this->updates;
    }

    private function getUpdatesForSql()
    {
        $updates = array();
        foreach ($this->updates as $key => $value) {
            $updates[] = $key . "='" . $value . "'";
        }
        return implode(",", $updates);
    }

    public function setId($id){
        $this->setValue($this->idField, $id);
    }
    public function getId()
    {
        return $this->id;
    }

    public function getTableName(){
        return $this->tableName;
    }

    public function construct(){
        $sql = "create table ". $this->getTableName()."(";
        $columns = array();
        foreach($this->config['columns'] as $name=>$type){
            $columns[] =  $name." ". $type;
        }
        $sql .= implode(",", $columns);
        $sql.=")";
        mysql_query($sql) or die($sql);
    }

    public function exists(){
        return mysql_num_rows(
            mysql_query("show tables like '". $this->getTableName() . "'")
        ) > 0;
    }

    public function drop(){
        mysql_query("drop table ". $this->getTableName());
    }

    public function deleteAll(){
        mysql_query("delete from ". $this->getTableName());
    }
}
