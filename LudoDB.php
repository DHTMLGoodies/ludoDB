<?php
/**
 * MySql DB layer
 * User: Alf Magne Kalleland
 * Date: 03.11.12
 * Time: 01:37
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

    /**
     * Return number of rows in query
     * @method countRows
     * @param String $sql
     * @return int
     */
    public function countRows($sql){
        return mysql_num_rows($this->query($sql));
    }

    /**
     * Get last insert id
     * @method getInsertId
     * @return int
     */
    public function getInsertId(){
        return mysql_insert_id();
    }

    public function getRows($sql){
        $ret = array();
        $result = $this->query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $ret[] = $row;
        }
        return $ret;
    }

    /**
     * Returns value of first column in query
     * @param $sql
     */
    public function getValue($sql){
        $result = $this->query($sql." limit 1");
        $row = mysql_fetch_row($result);
        if(isset($row))return $row[0];
        return null;
    }
}
