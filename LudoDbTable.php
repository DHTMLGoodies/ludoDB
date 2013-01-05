<?php
/**
 * Representation of a ludoDB table
 */
abstract class LudoDbTable extends LudoDBObject
{
    const DELETED = '__DELETED__';
    protected $idField = 'id';
    protected $config = array(
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
        )
    );

    private $id;
    private $data = array();
    private $updates;
    private $externalClasses = array();

    public function __construct($id = null)
    {
        parent::__construct();
        $this->config['lookupField'] = $this->idField;
        $this->populate($id);
    }

    public function populate($id)
    {
        if (!isset($id)) return;
        $data = $this->db->one($this->getSQL($id));
        if (isset($data)) {
            $this->populateWith($data);
        }
        $this->id = $id;
    }

    private function getSQL($id)
    {
        $this->config['lookupField'] = $this->idField;
        $sql = new LudoSQL($this->config, $id);
        return $sql->getSql();
    }

    private function populateWith($data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    protected function getValue($column)
    {
        if ($this->isExternalColumn($column)) {
            return $this->getExternalValue($column);
        }
        if (isset($this->updates) && isset($this->updates[$column])) {
            return $this->updates[$column] == self::DELETED ? null : $this->updates[$column];
        }
        return isset($this->data[$column]) ? $this->data[$column] : null;
    }

    private function isExternalColumn($column)
    {
        return isset($this->config['columns'][$column]) && is_array($this->config['columns'][$column]);
    }

    private function getExternalValue($column)
    {
        return $this->getExternalClassFor($column)->getValue();
    }

    /**
     * @param $column
     * @return LudoDBCollection table
     */
    private function getExternalClassFor($column)
    {
        if (!isset($this->externalClasses[$column])) {
            $class = $this->config['columns'][$column]['class'];
            $this->externalClasses[$column] = new $class($this->getId());
        }
        return $this->externalClasses[$column];
    }

    protected function setValue($column, $value)
    {
        if (!$this->hasColumn($column) || is_array($value)) return;
        if (!isset($value)) $value = self::DELETED;
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
            $this->data[$key] = $value === self::DELETED ? null : $value;
        }
        $this->updates = null;
        return $this->getId();
    }

    private function update()
    {
        if ($this->isValid()) {
            $sql = "update " . $this->getTableName() . " set " . $this->getUpdatesForSql() . " where " . $this->idField . " = '" . $this->getId() . "'";
            $this->db->query($sql);
        }
    }

    private function insert()
    {
        if ($this->isValid()) {
            $this->beforeInsert();

            $sql = "insert into " . $this->getTableName();
            $sql .= "(" . implode(",", array_keys($this->updates)) . ")";
            $sql .= "values('" . implode("','", array_values($this->updates)) . "')";
            $this->db->query($sql);
            $this->setId($this->db->getInsertId());
        }
    }

    /**
     * Method executed before new record is saved in db
     * @method beforeInsert
     */
    protected function beforeInsert(){

    }
    /**
     * Rollback updates
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
            $updates[] = $key . "=" . ($value === self::DELETED ? 'NULL' : "'" . $value . "'");
        }
        return implode(",", $updates);
    }

    protected function setId($id)
    {
        $this->id = $id;
        $this->data[$this->getIdField()] = $id;
    }

    public function getId()
    {
        return $this->id;
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
            if (is_string($type)) {
                $columns[] = $name . " " . $type;
            }
        }
        $sql .= implode(",", $columns) . ")";
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
        if ($this->exists()) {
            $this->db->query("drop table " . $this->getTableName());
        }
    }

    /**
     * Delete all records from database table
     * @method deleteTableData
     */
    public function deleteTableData()
    {
        $this->db->query("delete from " . $this->getTableName());
    }

    private function createIndexes()
    {
        if (!isset($this->config['indexes'])) return;
        $indexes = $this->config['indexes'];
        foreach ($indexes as $index) {
            $this->db->query("create index " . $this->getIndexName($index) . " on " . $this->getTableName() . "(" . $index . ")");
        }
    }

    private function getIndexName($field)
    {
        return 'IND_' . md5($this->getTableName() . $field);
    }

    private function insertDefaultData()
    {
        if (!isset($this->config['data'])) return;

        foreach ($this->config['data'] as $item) {
            $cl = $this->getNewInstance();
            foreach ($item as $key => $value) {
                $cl->setValue($key, $value);
            }
            $cl->commit();
        }
    }

    /**
     * @method getClassName
     * @return LudoDBTable class
     */
    private function getNewInstance()
    {
        $className = get_class($this);
        return new $className;
    }

    public function hasColumn($column)
    {
        return isset($this->config['columns'][$column]);
    }

    public function getIdField()
    {
        return $this->idField;
    }

    public function getJSON()
    {
        $columns = $this->config['columns'];
        $ret = array();
        foreach ($columns as $column => $def) {
            $ret[$column] = $this->getValue($column);
        }
        $ret = array_merge($ret, $this->getJoinColumns());
        return json_encode($ret);
    }

    private function getJoinColumns()
    {
        $ret = array();
        if (isset($this->config['join'])) {
            foreach ($this->config['join'] as $join) {
                foreach ($join['columns'] as $col) {
                    $ret[$col] = $this->getValue($col);
                }
            }
        }
        return $ret;
    }

    public function JSONPopulate(array $jsonAsArray)
    {
        foreach ($jsonAsArray as $key => $value) {
            $this->setValue($key, $value);
        }
        $this->commit();
    }

    public function isValid()
    {
        return true;
    }

    public function getColumn($column)
    {
        return $this->getExternalClassFor($column);
    }
}
