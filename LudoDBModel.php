<?php
/**
 * Representation of a ludoDB table
 */
abstract class LudoDBModel extends LudoDBObject
{

    private $id;
    private $data = array();
    private $updates;
    private $externalClasses = array();
    private $commitDisabled;
    private $populated = false;

    protected function onConstruct()
    {

    }

    protected function populate()
    {
        $this->populated = true;
        $this->constructorValues = $this->getValidConstructByValues($this->constructorValues);
        $data = $this->db->one($this->sqlHandler()->getSql());
        if (isset($data)) {
            $this->populateWith($data);
            $this->setId($this->getValue($this->parser->getIdField()));
        }
    }

    private function autoPopulate()
    {
        if (!$this->populated && isset($this->constructorValues)) {
            $this->populate();
        }
    }

    private function getValidConstructByValues($params)
    {
        $paramNames = $this->parser->getConstructorParams();
        for ($i = 0, $count = count($params); $i < $count; $i++) {
            $params[$i] = $this->getValidConstructByValue($paramNames[$i], $params[$i]);
        }
        return $params;
    }

    protected function getValidConstructByValue($key, $value)
    {
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
        $this->autoPopulate();
        if ($this->parser->isExternalColumn($column)) {
            return $this->getExternalValue($column);
        }
        if (isset($this->updates) && isset($this->updates[$column])) {
            return $this->updates[$column] == LudoSQL::DELETED ? null : $this->updates[$column];
        }
        return isset($this->data[$column]) ? $this->data[$column] : null;
    }

    private function getExternalValue($column)
    {
        $method = $this->parser->getGetMethod($column);
        return $this->getExternalClassFor($column)->$method();
    }

    /**
     * @param String $column
     * @return LudoDBCollection table
     */
    private function getExternalClassFor($column)
    {
        if (!isset($this->externalClasses[$column])) {
            $class = $this->parser->externalClassNameFor($column);
            $fk = $this->parser->foreignKeyFor($column);
            if (isset($fk)) {
                $val = $this->getValue($fk);
            } else {
                if (!$this->getId()) $this->commit();
                $val = $this->getId();
            }
            $this->externalClasses[$column] = new $class($val);
        }
        return $this->externalClasses[$column];
    }

    protected function setValue($column, $value)
    {
        if ($this->parser->isExternalColumn($column)) {
            $this->setExternalValue($column, $value);
        } else {
            $value = $this->db->escapeString($value);
            if (!isset($value)) $value = LudoSQL::DELETED;
            if (!isset($this->updates)) $this->updates = array();
            $this->updates[$this->parser->getInternalColName($column)] = $value;
        }
        return null;
    }

    private function setExternalValue($column, $value)
    {
        $method = $this->parser->getSetMethod($column);
        if (isset($method)) {
            $this->getExternalClassFor($column)->$method($value);
        }
    }

    public function disableCommit()
    {
        $this->commitDisabled = true;
    }

    public function enableCommit()
    {
        $this->commitDisabled = false;
    }

    public function commit()
    {
        if ($this->commitDisabled) return null;
        if (!isset($this->updates)) {
            if ($this->getId() || !$this->parser->isIdAutoIncremented()) {
                return null;
            }
        }
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
        if (isset($this->updates)) {
            foreach ($this->updates as $key => $value) {
                $this->data[$key] = $value === LudoSQL::DELETED ? null : $value;
            }
        }
        foreach ($this->externalClasses as $class) {
            $this->commitExternal($class);
        }
        $this->updates = null;
        return $this->getId();
    }

    /**
     * @param LudoDBObject $class
     */
    private function commitExternal($class)
    {
        $class->commit();
    }

    private function update()
    {
        if ($this->isValid()) {
            $this->beforeUpdate();
            $this->clearCache();
            $this->db->query($this->sqlHandler()->getUpdateSql());
        }
    }

    public function getUncommitted()
    {
        return $this->updates;
    }

    private function insert()
    {
        if ($this->isValid()) {
            $this->beforeInsert();
            $this->db->query($this->sqlHandler()->getInsertSQL());
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

    protected function setId($id)
    {
        $this->id = $id;
        $this->data[$this->parser->getIdField()] = $id;
        $this->externalClasses = array();
    }

    public function getId()
    {
        $this->autoPopulate();
        return $this->id;
    }

    /**
     * Create DB table
     * @method createTable
     */
    public function createTable()
    {
        $this->db->query($this->sqlHandler()->getCreateTableSql());
        $this->createIndexes();
        $this->insertDefaultData();
    }

    /**
     * Returns true if database table exists.
     * @return bool
     */
    public function exists()
    {
        return $this->db->tableExists($this->parser->getTableName());
    }

    private $riskyQuery;

    /**
     * Drop database table
     * @method drop
     */
    public function drop()
    {
        if ($this->exists()) {
            $this->riskyQuery = "drop table " . $this->parser->getTableName();
        }
        return $this;
    }

    public function deleteTableData()
    {
        $this->riskyQuery = "delete from " . $this->parser->getTableName();
        return $this;
    }

    /**
     * Execute risky query,
     * @example
     * $p = new Person();
     * $p->drop()->yesImSure();
     */
    public function yesImSure()
    {
        if (isset($this->riskyQuery)) {
            $this->db->query($this->riskyQuery);
            if ($this->JSONCaching) {
                LudoDBCache::clearCacheByClass(get_class($this));
                $json = new LudoDBCache();
                $json->deleteTableData()->yesImSure();
            }
            $this->riskyQuery = null;
        }
    }

    private function createIndexes()
    {
        $indexes = $this->parser->getIndexes();
        if (!isset($indexes)) return;
        foreach ($indexes as $index) {
            $this->db->query("create index " . $this->getIndexName($index) . " on " . $this->parser->getTableName() . "(" . $index . ")");
        }
    }

    private function getIndexName($field)
    {
        return 'IND_' . md5($this->parser->getTableName() . $field);
    }

    private function insertDefaultData()
    {
        $data = $this->parser->getDefaultData();
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
     * @return LudoDBModel class
     */
    private function getNewInstance()
    {
        $className = get_class($this);
        return new $className;
    }

    public function getSomeValuesFiltered($keys)
    {
        return $this->some($keys, true);
    }

    public function getSomeValues($keys)
    {
        return $this->some($keys, false);
    }

    private function some($keys, $filtered = false)
    {
        $ret = array();
        foreach ($keys as $key) {
            $col = $this->parser->getPublicColumnName($key);
            $val = $this->getValue($key);
            if (!$filtered || isset($val)) $ret[$col] = $val;
        }
        return $ret;
    }

    public function clearValues()
    {
        $this->id = null;
        $this->data = array();
        $this->updates = null;
    }

    public function getValues()
    {
        $this->autoPopulate();
        $columns = $this->parser->getColumns();
        $ret = array();
        foreach ($columns as $column => $def) {
            $colName = $this->parser->getPublicColumnName($column);
            $ret[$colName] = $this->getValue($column);
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

    public function isValid()
    {
        return true;
    }

    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) === 'set') {
            $col = $this->parser->getColumnByMethod($name);
            if (isset($col) && $this->parser->canWriteTo($col)) {
                return $this->setValue($col, $arguments[0]);
            }
        }
        if (substr($name, 0, 3) === 'get') {
            $col = $this->parser->getColumnByMethod($name);
            if (isset($col) && $this->parser->canReadFrom($col)) {
                return $this->getValue($col);
            }
        }
        throw new Exception("Invalid method call " . $name);
    }

    private $whereEqualsArray = null;

    public function where($column)
    {
        if ($this->parser->canBePopulatedBy($column)) {
            $this->createWhereEqualsArray();
            $this->whereEqualsArray['where'][] = $column;
        }
        return $this;
    }

    public function equals($value)
    {
        $this->createWhereEqualsArray();
        $index = count($this->whereEqualsArray['equals']);
        if (isset($this->whereEqualsArray['where'][$index])) {
            $this->whereEqualsArray['equals'][] = $value;
        }
        return $this;
    }

    private function createWhereEqualsArray()
    {
        if (!isset($this->whereEqualsArray)) {
            $this->whereEqualsArray = array(
                'where' => array(),
                'equals' => array()
            );
        }
    }

    /**
     * Populate an object dynamically, example
     * $pump = new WaterPump();
     * $pump->where('category')->equals('10)->where('brand')->equals('Toshiba')->create();
     *
     * @return LudoDBModel
     */
    public function create()
    {
        $this->parser->setConstructBy($this->whereEqualsArray['where']);
        $this->constructorValues = $this->whereEqualsArray['equals'];
        $this->whereEqualsArray = null;
        return $this;
    }

    public function setValues($data)
    {
        $valuesSet = false;
        foreach ($data as $column => $value) {
            if ($this->parser->canWriteTo($column)) {
                $this->setValue($column, $value);
                $valuesSet = true;
            }
        }
        return $valuesSet;
    }

    public function save($data)
    {
        try {
            $this->validate($data);
            $this->setValues($data);
            $this->commit();
            return array($this->parser->getIdField() => $this->getId());
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    protected function validate($data)
    {

    }

    /**
     * Delete record
     */
    public function delete()
    {
        if (isset($this->constructorValues) && count($this->constructorValues)) {
            $this->db->query($this->sqlHandler()->getDeleteSQL());
            $this->clearCache();
            $this->clearValues();
        }
    }
}
