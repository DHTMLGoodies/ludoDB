<?php
/**
 * Representation of a ludoDB table
 */
abstract class LudoDbTable extends LudoDBObject
{
    const DELETED = '__DELETED__';
    protected $config = array(
        'idField' => 'id',
        'columns' => array(
        )
    );

    private $id;
    private $data = array();
    private $updates;
    private $externalClasses = array();

    protected function onConstruct()
    {
        if (isset($this->constructorValues)) {
            $this->populate();
        }
    }


    public function populate()
    {
        $this->constructorValues = $this->getValidQueryParams($this->constructorValues);
        $data = $this->db->one($this->getSQL());
        if (isset($data)) {
            $this->populateWith($data);
            $this->setId($this->getValue($this->configParser()->getIdField()));
        }
    }

    protected function getValidQueryParams($params)
    {
        $paramNames = $this->configParser()->getConstructorParams();
        for($i=0,$count = count($params);$i<$count;$i++){
            $params[$i] = $this->getValidQueryParam($paramNames[$i], $params[$i]);
        }
        return $params;
    }

    protected function getValidQueryParam($key, $value){
        return $value;
    }

    private function getSQL()
    {
        $sql = new LudoSQL($this);
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
        if ($this->configParser()->isExternalColumn($column)) {
            return $this->getExternalValue($column);
        }
        if (isset($this->updates) && isset($this->updates[$column])) {
            return $this->updates[$column] == self::DELETED ? null : $this->updates[$column];
        }
        return isset($this->data[$column]) ? $this->data[$column] : null;
    }

    private function getExternalValue($column)
    {
        $method = $this->configParser()->getGetMethod($column);
        return $this->getExternalClassFor($column)->$method();
    }

    /**
     * @param $column
     * @return LudoDBCollection table
     */
    private function getExternalClassFor($column)
    {
        if (!isset($this->externalClasses[$column])) {
            $class = $this->configParser()->externalClassNameFor($column);
            $val = $this->configParser()->foreignKeyFor($column);
            if (isset($val)){
                $val = $this->getValue($val);
            }  else {
                if (!$this->getId()) $this->commit();
                $val = $this->getId();
            }
            $this->externalClasses[$column] = new $class($val);
        }
        return $this->externalClasses[$column];
    }

    protected function setValue($column, $value)
    {
        if ($this->configParser()->isExternalColumn($column)) {
            $this->setExternalValue($column, $value);
        } else {
            if (is_string($value)) $value = mysql_real_escape_string($value);
            if (!isset($value)) $value = self::DELETED;
            if (!isset($this->updates)) $this->updates = array();
            $this->updates[$column] = $value;
        }
    }

    private function setExternalValue($column, $value)
    {
        $method = $this->configParser()->getSetMethod($column);
        if (isset($method)) {
            $this->getExternalClassFor($column)->$method($value);
        }
    }

    public function commit()
    {
        if (!isset($this->updates)) {
            if ($this->getId() || !$this->configParser()->idIsAutoIncremented()) {
                return null;
            }
        }
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
        if(isset($this->updates)){
            foreach ($this->updates as $key => $value) {
                $this->data[$key] = $value === self::DELETED ? null : $value;
            }
        }
        foreach ($this->externalClasses as $class) {
            $class->commit();
        }
        $this->updates = null;
        return $this->getId();
    }

    private function update()
    {
        if ($this->isValid()) {
            $this->beforeUpdate();
            $sql = "update " . $this->configParser()->getTableName() . " set " . $this->getUpdatesForSql() . " where " . $this->configParser()->getIdField() . " = '" . $this->getId() . "'";
            $this->db->query($sql);
        }
    }

    private function getUpdatesForSql()
    {
        $updates = array();
        foreach ($this->updates as $key => $value) {
            $updates[] = $key . "=" . ($value === self::DELETED ? 'NULL' : "'" . $value . "'");
        }
        return implode(",", $updates);
    }

    private function insert()
    {
        if ($this->isValid()) {
            $this->beforeInsert();

            $sql = "insert into " . $this->configParser()->getTableName();
            if (!isset($this->updates)) {
                $sql.="(".$this->configParser()->getIdField().")values(NULL)";
            } else {
                $sql .= "(" . implode(",", array_keys($this->updates)) . ")";
                $sql .= "values('" . implode("','", array_values($this->updates)) . "')";

            }
            $this->db->query($sql);
            $this->setId($this->db->getInsertId());
        }
    }

    protected function beforeUpdate()
    {
    }

    /**
     * Method executed before new record is saved in db
     * @method beforeInsert
     */
    protected function beforeInsert()
    {
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

    protected function setId($id)
    {
        $this->id = $id;
        $this->data[$this->configParser()->getIdField()] = $id;
        $this->externalClasses = array();
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
        $sql = new LudoSQL($this);
        $this->db->query($sql->getCreateTableSql());

        $this->createIndexes();
        $this->insertDefaultData();
    }

    /**
     * Returns true if database table exists.
     * @return bool
     */
    public function exists()
    {
        return $this->db->tableExists($this->configParser()->getTableName());
    }

    /**
     * Drop database table
     * @method drop
     */
    public function drop()
    {
        if ($this->exists()) {
            $this->db->query("drop table " . $this->configParser()->getTableName());
        }
    }

    public function deleteTableData()
    {
        $this->db->query("delete from " . $this->configParser()->getTableName());
    }

    private function createIndexes()
    {
        $indexes = $this->configParser()->getIndexes();
        if (!isset($indexes)) return;
        foreach ($indexes as $index) {
            $this->db->query("create index " . $this->getIndexName($index) . " on " . $this->configParser()->getTableName() . "(" . $index . ")");
        }
    }

    private function getIndexName($field)
    {
        return 'IND_' . md5($this->configParser()->getTableName() . $field);
    }

    private function insertDefaultData()
    {
        $data = $this->configParser()->getDefaultData();
        if (!isset($data)) return;
        foreach ($data as $row) {
            $cl = $this->getNewInstance();
            foreach ($row as $key => $value) {
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

    public function __toString()
    {
        return json_encode($this->getValues());
    }

    protected function getValues()
    {
        $columns = $this->config['columns'];
        $ret = array();
        foreach ($columns as $column => $def) {
            $ret[$column] = $this->getValue($column);
        }
        return array_merge($ret, $this->getJoinColumns());
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
