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
        $this->setLookupField();
        if($id){
            $this->populate($id);
        }
    }

    private function setLookupField(){
        if(!isset($this->config['lookupField'])){
            $this->config['lookupField'] = $this->getIdField();
        }
    }

    public function populate($id)
    {
        $data = $this->db->one($this->getSQL($this->getValidId($id)));
        if (isset($data)) {
            $this->populateWith($data);
            $this->setId($this->getValue($this->getIdField()));
        }
    }

    protected function getValidId($id){
        return $id;
    }

    private function getSQL($id)
    {
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
        $method = $this->getMethodForExternalColumn($column);
        return $this->getExternalClassFor($column)->$method();
    }

    private function getMethodForExternalColumn($column){
        $method = $this->getColumnProperty($column, 'get');
        return isset($method) ? $method : 'getValues';
    }

    /**
     * @param $column
     * @return LudoDBCollection table
     */
    private function getExternalClassFor($column)
    {
        if (!isset($this->externalClasses[$column])) {
            $class = $this->config['columns'][$column]['class'];
            $val = $this->getColumnProperty($column, 'fk');
            if(isset($val))$val = $this->getValue($val); else $val = $this->getId();
            $this->externalClasses[$column] = new $class($val);
        }
        return $this->externalClasses[$column];
    }

    protected function setValue($column, $value)
    {
        if($this->isExternalColumn($column)){
            $this->setExternalValue($column, $value);
        }else{
            if (is_string($value)) $value = mysql_real_escape_string($value);
            if (!isset($value)) $value = self::DELETED;
            if (!isset($this->updates)) $this->updates = array();
            $this->updates[$column] = $value;
        }
    }

    private function setExternalValue($column, $value){
        $method = $this->getColumnProperty($column, 'set');
        if(isset($method)){
            $this->getExternalClassFor($column)->$method($value);
        }
    }

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
        foreach($this->externalClasses as $class){
            $class->commit();
        }
        $this->updates = null;
        return $this->getId();
    }

    private function update()
    {
        if ($this->isValid()) {
            $this->beforeUpdate();
            $sql = "update " . $this->getTableName() . " set " . $this->getUpdatesForSql() . " where " . $this->getIdField() . " = '" . $this->getId() . "'";
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

            $sql = "insert into " . $this->getTableName();
            $sql .= "(" . implode(",", array_keys($this->updates)) . ")";
            $sql .= "values('" . implode("','", array_values($this->updates)) . "')";
            $this->db->query($sql);
            $this->setId($this->db->getInsertId());
        }
    }

    protected function beforeUpdate(){

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

    protected function setId($id)
    {
        $this->id = $id;
        $this->data[$this->getIdField()] = $id;
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
        $sql = new LudoSQL($this->config);
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
        return $this->db->tableExists($this->getTableName());
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

    public function deleteTableData()
    {
        $this->db->query("delete from " . $this->getTableName());
    }

    private function createIndexes()
    {
        if (!isset($this->config['indexes'])) return;
        foreach ($this->config['indexes'] as $index) {
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

    public function getIdField(){
        return isset($this->config['idField']) ? $this->config['idField'] : 'id';
    }

    public function getJSON()
    {

        return json_encode($this->getValues());
    }

    protected function getValues(){
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

    private function getColumnProperty($column, $property){
        $val = $this->getClassProperty($column, $property);
        if(isset($val))return $val;
        $col = $this->config['columns'][$column];
        return isset($col[$property]) ? $col[$property] : null;
    }

    private function getClassProperty($column, $property){
        if(isset($this->config['classes']) && isset($this->config['classes'][$column])){
            $cl = $this->config['classes'];
            return isset($cl[$column]) && isset($cl[$column][$property]) ? $cl[$column][$property] : null;
        }
        return null;
    }


}
