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
        if (isset($ref)) $this->populate($ref);

    }

    private function populate($ref)
    {
        if (!isset($this->compiledSql)) {
            $this->compileSql();
        }
        if (is_numeric($ref)) {
            $data = $this->db->one($this->compiledSql . " and t." . $this->idField . "='" . $ref . "'");
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

    protected function getValue($column)
    {
        if (isset($this->updates) && isset($this->updates[$column])) {
            return $this->updates[$column];
        }
        return isset($this->data[$column]) ? $this->data[$column] : null;
    }

    protected function setValue($column, $value)
    {
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
        $this->id = mysql_insert_id();
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
        $this->setValue($this->idField, $id);
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
        $sql .= implode(",", $columns);
        $sql .= ")";
        mysql_query($sql) or die($sql);

        $this->createIndexes();
        $this->insertDefaultData();
    }

    /**
     * Returns true if database table exists.
     * @return bool
     */
    public function exists()
    {
        return mysql_num_rows(
            mysql_query("show tables like '" . $this->getTableName() . "'")
        ) > 0;
    }

    /**
     * Drop database table
     * @method drop
     */

    public function drop()
    {
        if($this->exists()){
            mysql_query("drop table " . $this->getTableName());
        }
    }

    /**
     * Delete all records from database table
     * @method deleteAll
     */
    public function deleteAll()
    {
        mysql_query("delete from " . $this->getTableName());
    }

    public function getCollection($key)
    {
        $config = $this->config['collections'][$key];
        $sql = " select ". $this->getColumnsForCollection($key).
            " from " . $config['table'] . " c where c." . $config['pk'] . "='" . $this->getValue($config['fk']) . "'";
        if (isset($config['orderBy'])) $sql .= " order by c." . $config['orderBy'];
        $result = $this->db->query($sql);

        $ret = array();
        while ($row = mysql_fetch_assoc($result)) {
            $ret[] = $row;
        }
        return $ret;
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
        $data = $this->config['data'];
        $className = get_class($this);
        foreach($data as $item){
            $cl = new $className();
            foreach($item as $key=>$value){
                $cl->setValue($key, $value);
            }
            $cl->commit();
        }
    }
}
