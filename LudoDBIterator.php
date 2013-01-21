<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 21.12.12
 * Time: 22:39
 */
class LudoDBIterator extends LudoDBObject implements Iterator
{
    private $loaded;
    private $dbResource;
    private $isValid;
    private $position;
    protected $currentRow;

    function rewind() {
        if ($this->dbResource) {
            $this->dbResource = null;
        }
        $this->position = -1;
        $this->loaded = false;
        $this->isValid = false;
    }

    /**
     * Return current value when iterating collection
     * @method current
     * @return mixed
     */
    public function current() {
        return $this->currentRow;
    }

    /**
     Return key used for iterator. default is numeric.
     @method key
     @return mixed
     @example
        function key(){
            return $this->currentRow['key']
        }
     to return key
     */
    function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
        $this->currentRow = $this->db->nextRow($this->dbResource);
        $this->isValid = $this->currentRow ? true : false;
    }

    public function valid() {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->isValid;
    }

    private function load(){
        $this->dbResource = $this->db->query($this->sqlHandler()->getSql());
        $this->loaded = true;
        $this->next();
    }

    /**
     * Return collection data
     * @method getValues
     * @return array
     */
    public function getValues(){
        $ret = array();
        foreach($this as $key=>$value){
            $ret[$key] = $value;
        }
        return $ret;
    }
}
