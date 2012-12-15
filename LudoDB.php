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
    public function query($sql){
        $res = mysql_query($sql) or die(mysql_error()."\nSQL:".$sql);
        return $res;
    }

    public function one($sql){
        $res = $this->query($sql);
        if($row = mysql_fetch_assoc($res)){
            return $row;
        }
        return null;
    }
}
