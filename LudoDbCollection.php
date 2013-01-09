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

    protected $config;

    protected function onConstruct($queryValues = null){
        if(isset($this->config['queryFields']) && !is_array($this->config['queryFields'])){
            $this->config['queryFields'] = array($this->config['queryFields']);
        }
    }

    protected function getSql()
    {
        $sql = new LudoSQL($this->config, $this->queryValues);
        return $sql->getSql();
    }

    public function deleteRecords(){
        if(isset($this->queryValues)){
            $this->db->query("delete from ". $this->getTableName()." where ". $this->config['queryFields'][0]."='". $this->queryValues[0]."'");
        }
    }
}
