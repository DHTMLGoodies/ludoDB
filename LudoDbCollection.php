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

    public function __construct($lookupValue = null){
        parent::__construct();
        $this->lookupValue = $lookupValue;

    }

    protected function getSql()
    {
        return 'select ' . $this->getColumns() . " from " . $this->getTableName() . $this->getWhere() . $this->getOrderBy();
    }



    private function getWhere()
    {
        if ($this->lookupValue) {
            return ' where ' . $this->config['lookupField'] . " = '" . $this->lookupValue . "'";
        }
        return '';
    }


}
