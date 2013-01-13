<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:31
 */
abstract class LudoDBCollection extends LudoDBIterator
{
    /**
     * Lookup value to use when instantiating collection. This value
     * is used in join with config['constructorParams']
     */
    protected $constructorValues;

    protected $config;

    public function deleteRecords(){
        if(isset($this->constructorValues)){
            $constructorParams = $this->configParser()->getConstructorParams();
            $this->db->query("delete from ". $this->configParser()->getTableName()." where ". $constructorParams[0]."='". $this->constructorValues[0]."'");
        }
    }
}
