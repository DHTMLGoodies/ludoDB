<?php
/**
 *
 * User: Alf Magne Kalleland
 * Date: 21.12.12
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Iterator class for LudoDBCollection
 * @package LudoDB
 */
class LudoDBIterator extends LudoDBObject implements Iterator
{
    /**
     * true when resource ref has been created
     * @var
     */
    private $loaded;
    /**
     * DB query resource reference
     * @var
     */
    private $dbResource;
    /**
     * is valid, i.e. has a row
     * @var
     */
    private $isValid;
    /**
     * Internal position reference
     * @var int
     */
    private $position;
    /**
     * Current row
     * @var array
     */
    protected $currentRow;
    /**
     * Array of returned rows.
     * @var
     */
    private $rows;

    /**
     * Internal data cache
     * @var array
     */
    private $valueCache;

    /**
     * Rewind iterator, i.e. start from beginning.
     */
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

    /**
     * Go to next row.
     */
    public function next() {
        ++$this->position;
        $this->currentRow = $this->db->nextRow($this->dbResource);
        $this->isValid = $this->currentRow ? true : false;
    }

    /**
     * Returns true when
     * @return bool
     */
    public function valid() {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->isValid;
    }

    /**
     * Execute query and get result set reference.
     */
    private function load(){
        $this->dbResource = $this->db->query($this->sqlHandler()->getSql(), $this->sqlHandler()->getArguments());
        $this->loaded = true;
        $this->next();
    }


    /**
     * Return collection data
     * @method getValues
     * @return array
     */
    public function getValues(){
        // TODO checkout the difference between $this->valueCache and $this->rows
        if(!isset($this->valueCache)){
            $this->clearStoredRows();
            $groupBy = $this->parser->getGroupBy();
            $this->valueCache = array();
            $staticValues = $this->parser->getStaticValues();
            foreach($this as $key=>$value){
                if(is_array($value)) $value = array_merge($value, $staticValues);
                if(isset($groupBy) && isset($value[$groupBy])){
                    if(!isset($this->valueCache[$groupBy])){
                        $this->valueCache[$groupBy] = array();
                    }
                    $this->valueCache[$groupBy][] = $value;
                    $this->storeRow($value);
                }else{
                    $this->valueCache[$key] = $value;
                    $this->storeRow($this->valueCache[$key]);
                }
            }
        }
        return $this->valueCache;
    }

    /**
     * Append current row to stored rows.
     * @param $row
     */
    protected function storeRow(&$row){
        $this->rows[] = &$row;
    }


    /**
     * Return rows as associated array where key is the value of one column.
     * @param $key
     * @return array
     */
    protected function getRowsAssoc($key){
        $rows = $this->getRows();
        $ret = array();
        foreach($rows as & $row){
            if(isset($row[$key])){
                $ret[$row[$key]] = & $row;
            }
        }
        return $ret;
    }

    /**
     * Returns reference to all tree nodes as numeric array
     * @return Array
     */
    public function getRows(){
        return $this->rows;
    }

    /**
     * Clear internal row array
     */
    protected function clearStoredRows(){
        $this->rows = array();
    }
}
