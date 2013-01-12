<?php
/**
 * Representation of a ludoDB table
 */
abstract class LudoDBTable extends LudoDBObject
{
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

    protected function populate()
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
            return $this->updates[$column] == LudoDB::DELETED ? null : $this->updates[$column];
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
            $fk = $this->configParser()->foreignKeyFor($column);
            if (isset($fk)){
                $val = $this->getValue($fk);
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
            $value = $this->db->escapeString($value);
            if (!isset($value)) $value = LudoDB::DELETED;
            if (!isset($this->updates)) $this->updates = array();
            $this->updates[$column] = $value;
        }
        return null;
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
            if ($this->getId() || !$this->configParser()->isIdAutoIncremented()) {
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
                $this->data[$key] = $value === LudoDB::DELETED ? null : $value;
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
    private function commitExternal($class){
        $class->commit();
    }

    private function update()
    {
        if ($this->isValid()) {
            $this->beforeUpdate();
            $this->db->update($this);
        }
    }

    public function getUncommitted(){
        return $this->updates;
    }

    private function insert()
    {
        if ($this->isValid()) {
            $this->beforeInsert();
            $this->db->insert($this);
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
        $columns = $this->configParser()->getColumns();
        $ret = array();
        foreach ($columns as $column => $def) {
            $colName = $this->configParser()->getPublicColumnName($column);
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

    public function JSONPopulate(array $jsonAsArray)
    {
        $this->setValues($jsonAsArray);
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

    public function __call($name, $arguments){
        if(substr($name,0,3) === 'set'){
            $col = $this->configParser()->getColumnByMethod($name);
            if(isset($col) && $this->configParser()->canWriteTo($col)){
                return $this->setValue($col, $arguments[0]);
            }
            $this->db->log("Invalid set method call " . $name."(".$col.")");
        }
        if(substr($name,0,3) === 'get'){
            $col = $this->configParser()->getColumnByMethod($name);
            if(isset($col) && $this->configParser()->canReadFrom($col)){
                return $this->getValue($col);
            }
        }

        throw new Exception("Invalid method call");
    }

    private $whereEqualsArray = null;

    public function where($column){
        if($this->configParser()->canBePopulatedBy($column)){
            $this->createWhereEqualsArray();
            $this->whereEqualsArray['where'][] = $column;
        }
        return $this;
    }

    public function equals($value){
        $this->createWhereEqualsArray();
        $index = count($this->whereEqualsArray['equals']);
        if(isset($this->whereEqualsArray['where'][$index])){
            $this->whereEqualsArray['equals'][] = $value;
        }
        return $this;
    }

    private function createWhereEqualsArray(){
        if(!isset($this->whereEqualsArray)){
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
     * @return LudoDBTable
     */
    public function create(){
        $this->configParser()->setConstructorParams($this->whereEqualsArray['where']);
        $this->constructorValues = $this->whereEqualsArray['equals'];
        $this->populate();
        $this->whereEqualsArray = null;
        return $this;
    }

    public function setValues($data){
        $valuesSet = false;
        foreach($data as $column=>$value){
            if($this->configParser()->canWriteTo($column)){
                $this->setValue($column, $value);
                $valuesSet = true;
            }
        }
        return $valuesSet;
    }

}
