<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * Time: 14:31
 */
class LudoDBObject
{
    public function getTableName()
    {
        return isset($this->tableName) ? $this->tableName : get_class($this);
    }
}
