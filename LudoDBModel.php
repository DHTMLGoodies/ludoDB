<?php
/**
 * Representation of a ludoDB table.
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Abstract LudoDBModel class
 * @package LudoDB
 * @example examples/cities/DemoCity.php
 */
abstract class LudoDBModel extends LudoDBObject
{
    /**
     * Record data
     * @var array
     */
    protected $data = array();
    /**
     * Uncommited data
     * @var array
     */
    protected $updates;
    /**
     * Array of external class references
     * @var array
     */
    private $externalClasses = array();
    /**
     * True when commit has been disabled, i.e. no saving will be done
     * for this object.
     * @var
     */
    private $commitDisabled;

    /**
     * True when object has been populated with data from db
     * @var bool
     */
    private $populated = false;

    /**
     * Risky delete table data or drop table sql query waiting for execution.
     * @var string
     */
    private $riskyQuery;

    /**
     * Populate with data from db
     */
    protected function populate()
    {
        $this->populated = true;
        $this->arguments = $this->getValidArguments($this->arguments);
        $data = $this->db->one($this->sqlHandler()->getSql(), $this->sqlHandler()->getArguments());
        if (isset($data)) {
            $this->populateWith($data);
            $this->setId($this->getValue($this->parser->getIdField()));
        }
    }

    /**
     * Validates constructor arguments.
     * @param $params
     * @return array
     */
    private function getValidArguments($params)
    {
        $paramNames = $this->parser->getConstructorParams();
        for ($i = 0, $count = count($params); $i < $count; $i++) {
            $params[$i] = $this->getValidArgument($paramNames[$i], $params[$i]);
        }
        return $params;
    }

    /**
     * Return valid value for argument with given name
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function getValidArgument($key, $value)
    {
        return $value;
    }

    /**
     * Populate model with these data
     * @param array $data
     */
    private function populateWith($data = array())
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Return column value. This method will return
     * * Uncommitted value if exists or value from db
     * * Value from external models/collection
     * * Value of static columns
     * * Default values.
     * Example:
     * <code>
     * public function getFirstname(){
     *      return $this->getValue('firstname');
     * }
     * </code>
     * @param $column
     * @return null
     */
    protected function getValue($column)
    {
        $this->autoPopulate();
        if($this->parser->isStaticColumn($column)){
            return $this->parser->getStaticValue($column);
        }
        if ($this->parser->isExternalColumn($column)) {
            return $this->getExternalValue($column);
        }
        if (isset($this->updates) && isset($this->updates[$column])) {
            return $this->updates[$column] == LudoDBSql::DELETED ? null : $this->updates[$column];
        }
        return isset($this->data[$column]) ? $this->data[$column] : $this->parser->getDefaultValue($column);
    }

    /**
     * Auto populate model with data from db.
     */
    private function autoPopulate()
    {
        if (!$this->populated && !empty($this->arguments)) {
            $this->populate();
        }
    }

    /**
     * Return external value
     * @param $column
     * @return mixed
     */
    private function getExternalValue($column)
    {
        $method = $this->parser->getGetMethod($column);
        return $this->getExternalClassFor($column)->$method();
    }

    /**
     * Return external class reference for external column
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

    /**
     * Set a column value. This value will not be committed to db until
     * a call to commit is made.
     * Example:
     * <code>
     * public function setFirstName($name){
     *      $this->setValue('firstname', $name');
     * }
     * </code>
     * @param $column
     * @param $value
     * @return null
     */
    protected function setValue($column, $value)
    {
        if ($this->parser->isExternalColumn($column)) {
            $this->setExternalValue($column, $value);
        } else {
            $value = $this->db->escapeString($value);
            if (!isset($this->updates)) $this->updates = array();
            $this->updates[$this->parser->getInternalColName($column)] = $value;
        }
        return null;
    }

    /**
     * Update column value of external column
     * @param $column
     * @param $value
     */
    private function setExternalValue($column, $value)
    {
        $method = $this->parser->getSetMethod($column);
        if (isset($method)) {
            $this->getExternalClassFor($column)->$method($value);
        }
    }

    /**
     * Disable commit for this object
     */
    public function disableCommit()
    {
        $this->commitDisabled = true;
    }

    /**
     * Enable commit for this object. commit is by default enabled
     */
    public function enableCommit()
    {
        $this->commitDisabled = false;
    }

    /**
     * Commit changes to the database.
     * Example:
     * <code>
     * $person = new Person();
     * $person->setFirstname('John');
     * $person->setLastname('Johnson');
     * $person->commit();
     * echo $person->getId();
     * </code>
     * @return null|void
     */
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
                $this->data[$key] = $value === LudoDBSql::DELETED ? null : $value;
            }
        }
        foreach ($this->externalClasses as $class) {
            $this->commitExternal($class);
        }
        $this->updates = null;
        return $this->getId();
    }

    /**
     * Execute commit on classes for external columns.
     * @param LudoDBObject $class
     */
    private function commitExternal($class)
    {
        $class->commit();
    }

    /**
     * Internal update method
     */
    private function update()
    {
        LudoDBValidator::getInstance()->validateUpdate($this);

        if ($this->isValid()) {
            $this->beforeUpdate();
            $this->clearCache();
            $this->db->query($this->sqlHandler()->getUpdateSql(), isset($this->updates) ? array_values($this->updates) : null);
        }
    }

    /**
     * Return uncommited data
     * @return array
     */
    public function getUncommitted()
    {
        return $this->updates;
    }

    /**
     * Private insert method
     */
    private function insert()
    {
        LudoDBValidator::getInstance()->validateSave($this);

        if ($this->isValid()) {
            $this->beforeInsert();
            $this->db->query($this->sqlHandler()->getInsertSQL(), isset($this->updates) ? array_values($this->updates) : null);
            $this->setId($this->db->getInsertId());
        }
    }

    /**
     * Method executed before record is updated
     */
    protected function beforeUpdate()
    {
    }

    /**
     * Method executed before new record is saved in db
     */
    protected function beforeInsert()
    {
    }

    /**
     * Rollback updates
     */
    public function rollback()
    {
        $this->updates = null;
    }

    /**
     * Update id field
     * @param $id
     */
    protected function setId($id)
    {
        $field = $this->parser->getIdField();
        if(!isset($this->data[$field])){
            $this->data[$field] = $id;
            $this->externalClasses = array();
        }
    }

    /**
     * Return id of current record.
     * @return string|int|null
     */
    public function getId()
    {
        $this->autoPopulate();
        $field = $this->parser->getIdField();
        return isset($this->data[$field]) ? $this->data[$field] : null;
    }

    /**
     * Create DB table
     */
    public function createTable()
    {
        $this->db->query($this->sqlHandler()->getCreateTableSql(), $this->parser->getDefaultValues());
        $this->createIndexes();
        $this->insertDefaultData();
    }

    public function getSQLCreate(){
        $sql = $this->sqlHandler()->getCreateTableSql();
        $params = $this->parser->getDefaultValues();
        if(!empty($params)){
            $sql = LudoDBSql::fromPrepared($sql, $params);
        }
        return $sql;
    }

    /**
     * Returns true if database table exists.
     * @return bool
     */
    public function exists()
    {
        return $this->db->tableExists($this->parser->getTableName());
    }



    /**
     * Drop database table. You need to call yesImSure afterwards.
      * Example:
      * <code>
      * $person = new Person();
      * $person->drop()->yesImSure();
      * </code>
     */
    public function drop()
    {
        if ($this->exists()) {
            $this->riskyQuery = "drop table " . $this->parser->getTableName();
        }
        return $this;
    }

    /**
     * Delete all data from this table. You need to call yesImSure afterwards.
     * Example:
     * <code>
     * $person = new Person();
     * $person->deleteTableData()->yesImSure();
     * </code>
     * @return LudoDBModel
     */
    public function deleteTableData()
    {
        $this->riskyQuery = "delete from " . $this->parser->getTableName();
        return $this;
    }

    /**
     * Executes drop or deleteTableData
     * @example
     * $p = new Person();
     * $p->drop()->yesImSure();
     */
    public function yesImSure()
    {
        if (isset($this->riskyQuery)) {
            $this->db->query($this->riskyQuery);
            if ($this->shouldCache("read")) {
                LudoDBCache::clearByClass(get_class($this));
                $json = new LudoDBCache();
                $json->deleteTableData()->yesImSure();
            }
            $this->riskyQuery = null;
        }
    }

    /**
     * Create database indexes defined in table config
     */
    private function createIndexes()
    {
        $indexes = $this->parser->getIndexes();
        if (!isset($indexes)) return;
        foreach ($indexes as $index) {
            $this->db->query("create index " . $this->getIndexName($index) . " on " . $this->parser->getTableName() . "(" . $index . ")");
        }
    }

    /**
     * Returns unique index name
     * @param $field
     * @return string
     */
    private function getIndexName($field)
    {
        return 'IND_' . md5($this->parser->getTableName() . $field);
    }

    /**
     * Populate database table with default data defined in table config
     */
    protected function insertDefaultData()
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
     * Return new instance of this LudoDBModel
     * @return LudoDBModel class
     */
    private function getNewInstance()
    {
        $className = get_class($this);
        return new $className;
    }

    /**
     * Return key-pair values with null values removed.
     * @param array $keys
     * @return array
     */
    public function getSomeValuesFiltered(array $keys)
    {
        return $this->some($keys, true);
    }

    /**
     * Return model values.
     * @param array $keys
     * @return array
     */
    public function getSomeValues(array $keys)
    {
        return $this->some($keys, false);
    }

    /**
     * Return values for these keys. When $filtered is true, onlye
     * columns with values(not null) will be returned.
     * @param array $keys
     * @param bool $filtered
     * @return array
     */
    private function some(array $keys, $filtered = false)
    {
        $ret = array();
        foreach ($keys as $key) {
            $col = $this->parser->getPublicColumnName($key);
            $val = $this->getValue($key);
            if ($this->parser->canReadFrom($col)) {
                if (!$filtered || isset($val)) $ret[$col] = $val;
            }

        }
        return $ret;
    }

    /**
     * Clear data from model.
     */
    public function clearValues()
    {
        $this->data = array();
        $this->updates = null;
    }

    /**
     * Return value of public columns
     * @return array
     */
    public function getValues()
    {
        $this->autoPopulate();
        $columns = $this->parser->getColumns();
        $ret = array();
        foreach ($columns as $column => $def) {
            $colName = $this->parser->getPublicColumnName($column);
            if ($this->parser->canReadFrom($colName)) {
                $ret[$colName] = $this->getValue($column);
            }
        }
        if($this->parser->hasStaticColumns()){
            $ret = array_merge($ret, $this->parser->getStaticValues());
        }
        return array_merge($ret, $this->getJoinColumns());
    }

    /**
     * Return values from joined columns.
     * @return array
     */
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

    /**
     * Return true if update and save is allowed to run.
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Creates magic get and set methods for columns. Example
     * columns "firstname" will have it's own "getFirstname" and "setFirstname" method.
     * @param $name
     * @param $arguments
     * @return null
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) === 'set' && $name !== "setId") {
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

    /**
     * Populate columns you can write to with these data
     * @example:
     * <code>
     * $person = new Person(1);
     * $person->populate(array(
     *  "firstname" => "Jane", "lastname" => "Johnson"
     * ));
     * $person->commit();
     * </code>
     * @param $data
     * @return bool
     */
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

    /**
     * Populate and save data. Returns array "<idField>" => "<id>"
     * Example:
     * <code>
     * $city = new City();
     * $data = $city->save(array("city" => "Stavanger", "country" => "Norway"));
     * var_dump($data);
     * </code>
     * @param $data
     * @return array
     */
    public function save($data)
    {
        if (empty($data)) return array();



        $idField = $this->parser->getIdField();
        if (isset($data[$idField])) $this->setId($data[$idField]);

        $this->setValues($data);
        $this->commit();

        return array($idField => $this->getId());
    }

    /**
     * Delete record
     */
    public function delete()
    {
        if ($this->getId()) {
            $this->db->query("delete from " . $this->parser->getTableName() . " where " . $this->parser->getIdField() . " = ?", $this->getId());
            $this->clearCache();
            $this->clearValues();
        }
    }
}
