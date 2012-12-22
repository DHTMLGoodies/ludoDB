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
        $sql = new LudoSQL($this->config, $this->lookupValue);
        return $sql->getSql();
    }
}
