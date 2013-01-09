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
     * is used in join with config['queryFields']
     */
    protected $queryValues;

    public function __construct($queryValues = null){
        parent::__construct();
        $this->queryValues = $queryValues;
    }

    protected function getSql()
    {
        $sql = new LudoSQL($this->config, $this->queryValues);
        return $sql->getSql();
    }
}
