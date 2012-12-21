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

    public function getTableName()
    {
        return isset($this->tableName) ? $this->tableName : get_class($this);
    }

    protected function getOrderBy()
    {
        return isset($this->config['orderBy']) ? ' order by ' . $this->config['orderBy'] : '';
    }

    protected function getColumns()
    {
        if (isset($this->config['columns'])) return implode(",", $this->config['columns']);
        return '*';
    }
}
