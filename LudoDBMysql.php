<?php
/**
 * PDO Mysql adapter. Should only be used when neither
 * PDO or MySqlI is supported on the server.
 * User: Alf Magne
 * Date: 01.02.13
 * Time: 12:26
 */
class LudoDBMysql extends LudoDB implements LudoDBAdapter
{
    public function connect()
    {
        self::$conn = mysql_connect(self::getHost(), self::getUser(), self::getPassword());
        mysql_select_db(self::getDb(), self::$conn);
    }

    public function escapeString($string)
     {
         if (is_string($string)) {
             $string = stripslashes($string);
             return mysql_real_escape_string($string);
         }
         return $string;
     }

    /**
         * @param $sql
         * @param array $params
         * @return bool|mysqli_result|resource|PDOStatement
         * @throws Exception
         */
        public function query($sql, $params = array())
        {
            if ($this->debug) $this->log($sql);
            if (self::$loggingEnabled) {
                self::$queryCounter++;
            }
            if(!empty($params)){
                $sql = LudoSQL::fromPrepared($sql, $params);
            }
            $res = mysql_query($sql) or die(mysql_error() . "\nSQL:" . $sql);
            return $res;
        }

    public function one($sql, $params = array())
    {
        if ($this->debug) $this->log($sql);
        $res = $this->query($sql . " limit 1", $params);
        if ($row = mysql_fetch_assoc($res)) {
            return $row;
        }
        return null;
    }
    public function countRows($sql, $params = array())
    {
        $res = $this->query($sql, $params);
        return mysql_num_rows($res);
    }

    /**
     * Get last insert id
     * @method getInsertId
     * @return int
     */
    public function getInsertId()
    {
        return mysql_insert_id();
    }

    /**
     * @param mysqli_result|resource|PDOStatement $result
     * @return array
     */
    public function nextRow($result)
    {
        return mysql_fetch_assoc($result);
    }

    /**
     * @param $sql
     * @return null|array
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql . " limit 1", $params);
        $row = mysql_fetch_row($result);
        return (isset($row)) ? $row[0] : null;
    }
}
