<?php
/**
 * MysqlI adapter. PDO is the preferred adapter and should be used
 * when supported by the server.
 * User: Alf Magne
 * Date: 01.02.13
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */
class LudoDBMySqlI extends LudoDB implements LudoDBAdapter
{
    /**
     * @var mysqli
     */
    protected static $conn;

    public function connect(){
        $host = LudoDBRegistry::get('DB_HOST');
        $user = LudoDBRegistry::get('DB_USER');
        $pwd = LudoDBRegistry::get('DB_PWD');
        $db = LudoDBRegistry::get('DB_NAME');
        self::$conn = new mysqli($host, $user, $pwd, $db);
    }

    /**
     * @param $sql
     * @param array $params
     * @return bool|mysqli_result
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
        if ($res = self::$conn->query($sql)) {
            return $res;
        }
        throw new Exception("SQL ERROR: " . self::$conn->error . "(" . $sql . ")");
    }
    /**
     * @param $sql
     * @return array|null
     */
    public function one($sql, $params = array())
    {
        if ($this->debug) $this->log($sql);
        $res = $this->query($sql . " limit 1", $params);
        if ($res && $row = $res->fetch_assoc()) {
            return $row;
        }
        return null;
    }
    /**
     * @param $sql
     * @return int
     */
    public function countRows($sql, $params = array())
    {
        $res = $this->query($sql, $params);
        return ($res) ? $res->num_rows : 0;
    }

    /**
     * Get last insert id
     * @method getInsertId
     * @return int
     */
    public function getInsertId()
    {
        return self::$conn->insert_id;
    }
    /**
     * @param mysqli_result|resource|PDOStatement $result
     * @return array
     */
    public function nextRow($result)
    {
        return $result->fetch_assoc();
    }

    /**
     * @param $sql
     * @return null|array
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql . " limit 1", $params);
        $row = $result->fetch_row();
        return (isset($row)) ? $row[0] : null;
    }

    public function escapeString($string)
    {
        if (is_string($string)) {
            $string = stripslashes($string);
            return self::$conn->escape_string($string);
        }
        return $string;
    }

}
