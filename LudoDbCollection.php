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
     * is used in join with config['constructorParams']
     */
    protected $constructorValues;

    protected $config;

    protected function getSql()
    {
        $sql = new LudoSQL($this);
        return $sql->getSql();
    }

    public function deleteRecords(){
        if(isset($this->constructorValues)){
            $this->db->query("delete from ". $this->getTableName()." where ". $this->config['constructorParams'][0]."='". $this->constructorValues[0]."'");
        }
    }
}
