<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:31
 */
class LudoDbCollection extends LudoDBObject implements Iterator
{
    protected $db;
    protected $lookupValue;
    protected $ref;
    protected $config = array();
    protected $data = null;
    private $position = 0;

    public function __construct(LudoDbTable $ref)
    {
        $this->db = new LudoDB();
        $this->ref = $ref;
        $this->position = 0;
    }

    public function setLookupValue($val)
    {
        $this->lookupValue = $val;
    }

    public function getValue()
    {
        if (!isset($this->data)) {
            if ($this->config['returnNumeric']) {
                $this->data = $this->getNumericArray();
            } else {
                $this->data = $this->getRows();
            }
        }
        return $this->data;
    }

    private function getNumericArray()
    {
        $result = $this->getResult();
        $ret = array();
        while ($row = $this->db->nextRow($result)) {
            $ret[] = $row[$this->config['columns'][0]];
        }
        return $ret;
    }

    protected function getResult()
    {
        return $this->db->query($this->getSql());
    }

    protected function getRows()
    {
        return $this->db->getRows($this->getSql());
    }

    private function getSql()
    {
        return 'select ' . $this->getColumns() . " from " . $this->getTableName() . $this->getWhere() . $this->getOrderBy();
    }

    private function getColumns()
    {
        if (isset($this->config['columns'])) return implode(",", $this->config['columns']);
        return '*';
    }

    private function getWhere()
    {
        if (isset($this->config['lookupField'])) {
            return ' where ' . $this->config['lookupField'] . " = '" . $this->lookupValue . "'";
        }
        return '';
    }

    private function getOrderBy()
    {
        return isset($this->config['orderBy']) ? ' order by ' . $this->config['orderBy'] : '';
    }

    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->data[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->data[$this->position]);
    }
}
