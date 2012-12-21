<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:31
 */
class LudoDbCollection extends LudoDbIterator
{
    protected $lookupValue;
    protected $ref;
    private $filter;
    protected $config = array(
        'columns' => array()
    );

    public function __construct($lookupValue){
        parent::__construct();
        $this->lookupValue = $lookupValue;

    }

    protected function getResult()
    {
        return $this->db->query($this->getSql());
    }

    protected function getSql()
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
        if ($this->lookupValue) {
            return ' where ' . $this->config['lookupField'] . " = '" . $this->lookupValue . "'";
        }
        return '';
    }

    private function getOrderBy()
    {
        return isset($this->config['orderBy']) ? ' order by ' . $this->config['orderBy'] : '';
    }
}
