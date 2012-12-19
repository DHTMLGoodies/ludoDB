<?php
/**
 * Representation of a ludoDB table
 */
abstract class LudoDbTable
{
    protected $db;
    protected $tableName;
    protected $idField = 'id';
    protected $config = array(
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
        )
    );

    private $id;
    private $data = array();
    private $updates;
    private $compiledSql = null;

    public function __construct($id = null)
    {
        $this->db = new LudoDb();
        $this->populate($id);
    }

    public function populate($id)
    {
        if(!isset($id))return;
        if (!isset($this->compiledSql)) {
            $this->compileSql();
        }
        $data = $this->db->one($this->compiledSql . " and t." . $this->idField . "='" . $id . "'");
        if (isset($data)) {
            $this->populateWith($data);
        }
        $this->id = $id;
    }

    private function compileSql()
    {
        $sql = "select t.*";
        $joins = $this->getSQLJoin();
        if (count($joins['columns'])) {
            $sql .= ',' . implode(",", $joins['columns']);
        }
        $sql .= " from " . $this->tableName . " t";
        if (count($joins['from'])) {
            $sql .= "," . implode($joins['from'], ",");
        }
        $sql .= " where 1=1";
        if (count($joins['where'])) {
            $sql .= " and " . implode($joins['where'], ' and ');
        }
        $this->compiledSql = $sql;
    }


    private function getSQLJoin()
    {
        $ret = array(
            'columns' => array(),
            'from' => array(),
            'where' => array()
        );
        if (!isset($this->config['join'])) return $ret;
        $joins = $this->config['join'];
        $i = 1;
        foreach ($joins as $join) {
            $ret['columns'][] = 't' . $i . "." . implode($join['columns'], ",t" . $i . ".");
            $ret['from'][] = $join['table'] . " t" . $i;
            $ret['where'][] = 't.' . $join['fk'] . "=t" . $i . "." . $join['pk'];
            $i++;
        }
        return $ret;
    }

    private function populateWith($data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    protected function getValue($column)
    {
        if (isset($this->updates) && isset($this->updates[$column])) {
            return $this->updates[$column];
        }
        return isset($this->data[$column]) ? $this->data[$column] : null;
    }

    protected function setValue($column, $value)
    {
        if(!$this->hasColumn($column))return;
        if (!isset($this->updates)) $this->updates = array();
        if (is_string($value)) $value = mysql_real_escape_string($value);
        $this->updates[$column] = $value;
    }

    /**
     * Commit changes to database
     * @method commit
     * @return {String|Number} id
     */
    public function commit()
    {
        if (!isset($this->updates)) return null;
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
        foreach ($this->updates as $key => $value) {
            $this->data[$key] = $value;
        }
        $this->updates = null;
        return $this->getId();
    }

    private function update()
    {
        $sql = "update " . $this->tableName . " set " . $this->getUpdatesForSql() . " where " . $this->idField . " = '" . $this->getId() . "'";
        $this->db->query($sql);
    }

    private function insert()
    {
        $sql = "insert into " . $this->tableName;
        $sql .= "(" . implode(",", array_keys($this->updates)) . ")";
        $sql .= "values('" . implode("','", array_values($this->updates)) . "')";
        $this->db->query($sql);
        $this->setId($this->db->getInsertId());
    }

    /**
     * Rollback changes
     * @method rollback
     */
    public function rollback()
    {
        $this->updates = null;
    }

    public function getUpdates()
    {
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

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Create DB table
     * @method createTable
     */
    public function createTable()
    {
        $sql = "create table " . $this->getTableName() . "(";
        $columns = array();
        foreach ($this->config['columns'] as $name => $type) {
            $columns[] = $name . " " . $type;
        }
        $sql .= implode(",", $columns) .")";
        $this->db->query($sql);

        $this->createIndexes();
        $this->insertDefaultData();
    }

    /**
     * Returns true if database table exists.
     * @return bool
     */
    public function exists()
    {
        return $this->db->countRows("show tables like '" . $this->getTableName() . "'") > 0;
    }

    /**
     * Drop database table
     * @method drop
     */

    public function drop()
    {
        if($this->exists()){
            $this->db->query("drop table " . $this->getTableName());
        }
    }

    /**
     * Delete all records from database table
     * @method deleteAll
     */
    public function deleteAll()
    {
        $this->db->query("delete from " . $this->getTableName());
    }

    public function getCollection($key)
    {
        $config = $this->config['collections'][$key];
        $sql = " select ". $this->getColumnsForCollection($key).
            " from " . $config['table'] . " c where c." . $config['pk'] . "='" . $this->getValue($config['fk']) . "'";
        if (isset($config['orderBy'])) $sql .= " order by c." . $config['orderBy'];
        return $this->db->getRows($sql);
    }

    private function getColumnsForCollection($collection){
        $c = $this->config['collections'][$collection];
        return isset($c['columns']) ? "c." . implode(",c.", $c['columns']) : 'c.*';
    }

    private function createIndexes()
    {
        if (!isset($this->config['indexes'])) return;
        $indexes = $this->config['indexes'];
        foreach ($indexes as $index) {
            $this->db->query("create index " . $this->getIndexName($index) . " on " . $this->tableName . "(" . $index . ")");
        }
    }

    private function getIndexName($field)
    {
        return 'IND_' . md5($this->tableName . $field);
    }

    private function insertDefaultData(){
        if(!isset($this->config['data']))return;
        $className = get_class($this);
        foreach($this->config['data'] as $item){
            $cl = new $className;
            foreach($item as $key=>$value){
                $cl->setValue($key, $value);
            }
            $cl->commit();
        }
    }

    public function hasColumn($column){
        return isset($this->config['columns'][$column]);
    }

    public function getIdField(){
        return $this->idField;
    }

    public function getJSON(){
        $columns = $this->config['columns'];
        $ret = array();
        foreach($columns as $column => $def){
            $ret[$column] = $this->getValue($column);
        }
        return json_encode($ret);
    }

    public function JSONPopulate(array $jsonAsArray){
        foreach($jsonAsArray as $key=>$value){
            $this->setValue($key, $value);
        }
        $this->commit();
    }
}
