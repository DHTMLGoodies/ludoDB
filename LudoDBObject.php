<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * Time: 14:31
 */
class LudoDBObject
{
    protected $db;
    protected $config = array();

    protected $queryValues;

    public function __construct(){
        $this->db = new LudoDb();
        if(func_num_args() > 0){
            $this->queryValues = func_get_args();
        }
        $this->onConstruct();
    }

    protected function onConstruct(){

    }

    public function getQueryValues(){
        return $this->queryValues;
    }

    public function getTableName()
    {
        return isset($this->config['table']) ? $this->config['table'] : get_class($this);
    }

    public function commit(){

    }

    public function getConfig(){
        return $this->config;
    }
}
