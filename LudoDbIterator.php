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
    protected $currentRow;
    private $singleValue;

    protected $config = array(
        'columns' => array()
    );

    public function __construct(){
        parent::__construct();
        $this->singleValue = count($this->config['columns']) === 1;
    }

    function rewind() {
        if ($this->dbResource) {
            $this->dbResource = null;
        }
        $this->position = -1;
        $this->loaded = false;
        $this->_valid = false;
    }

    /**
     * Return current value when iterating collection
     * @method current
     * @return mixed
     */
    function current() {
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
        $this->_valid = $this->currentRow ? true : false;
        if($this->singleValue && $this->currentRow){
            $this->db->log('Single value');
            $this->currentRow = array_values($this->currentRow);
            $this->currentRow = $this->currentRow[0];
        }
    }

    public function valid() {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->_valid;
    }

    private function load(){
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
