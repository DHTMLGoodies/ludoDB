<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:31
 */
abstract class LudoDbCollection extends LudoDbIterator
{
    /**
     * Lookup value to use when instantiating collection. This value
     * is used in join with config['lookupField']
     */
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
