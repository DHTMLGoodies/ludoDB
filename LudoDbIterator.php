<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 21.12.12
 * Time: 22:39
 */
abstract class LudoDbIterator extends LudoDBObject implements Iterator
{
    private $loaded;
    private $dbResource;
    private $_valid;
    private $position;
    private $currentRow;

    public function __construct(){
        $this->db = new LudoDB();
    }

    function rewind() {
        $this->db->log('Rewind');
        if ($this->dbResource) {
            $this->dbResource = null;
        }
        $this->position = -1;
        $this->loaded = false;
        $this->_valid = false;
    }

    function current() {
        $this->db->log('current');
        return $this->currentRow;
    }

    function key() {
        $this->db->log('key');
        return $this->position;
    }

    public function next() {
        $this->db->log('next');
        ++$this->position;
        $this->currentRow = $this->db->nextRow($this->dbResource);
        $this->_valid = $this->currentRow ? true : false;
    }

    public function valid() {
        $this->db->log('valid');
        if (!$this->loaded) {
            $this->load();
        }
        return $this->_valid;
    }

    private function load(){

        $this->db->log('Load');
        $this->dbResource = $this->db->query($this->getSql());
        $this->loaded = true;
        $this->next();
    }

    public function getValue(){
        $ret = array();
        foreach($this as $key=>$value){
            $ret[$key] = $value;
        }
        return $ret;
    }

    protected abstract function getSql();
}
