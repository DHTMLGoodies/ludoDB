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

    public function __construct(){
        $this->db = new LudoDb();
    }

    public function getTableName()
    {
        return isset($this->config['table']) ? $this->config['table'] : get_class($this);
    }
}
