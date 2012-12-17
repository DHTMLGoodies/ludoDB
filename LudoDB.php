<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 03.11.12
 * Time: 01:37
 * To change this template use File | Settings | File Templates.
 */
class LudoDB
{
    /**
     * Returns mySql result
     * @method query
     * @param {String} $sql
     * @return resource
     */
    public function query($sql){
        $res = mysql_query($sql) or die(mysql_error()."\nSQL:".$sql);
        return $res;
    }

    /**
     * Returns one row from sql query
     * @method one
     * @param {String} $sql
     * @return {Array} row
     */
    public function one($sql){
        $res = $this->query($sql." limit 1");
        if($row = mysql_fetch_assoc($res)){
            return $row;
        }
        return null;
    }
}
