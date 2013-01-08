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
    private $isValid;
    private $position;
    protected $currentRow;
    private $singleValue;

    protected $config = array(
        'columns' => array()
    );

    public function __construct(){
        parent::__construct();
        $this->singleValue = count($this->config['columns']) === 1 && !isset($this->config['join']);
    }

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
        $this->isValid = $this->currentRow ? true : false;
        if($this->singleValue && $this->currentRow){
            $this->currentRow = array_values($this->currentRow);
            $this->currentRow = $this->currentRow[0];
        }
    }

    public function valid() {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->isValid;
    }

    private function load(){
        $this->dbResource = $this->db->query($this->getSql());
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
            if(isset($value))$ret[$key] = $value;
        }
        return $ret;
    }

    protected abstract function getSql();
}
