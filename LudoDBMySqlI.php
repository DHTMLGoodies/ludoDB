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
        try{
            self::$conn = new mysqli(self::getHost(), self::getUser(), self::getPassword(), self::getDb());
        }catch(Exception $e){
             throw new LudoDBConnectionException("Could not connect to database because ". $e->getMessage(),400);
        }
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
     * @param array $params
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
     * @param array $params
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
     * @param array $params
     * @return null
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql . " limit 1", $params);
        $row = $result->fetch_row();
        return (isset($row)) ? $row[0] : null;
    }

    public function escapeString($string)
    {
        return is_string($string) ? self::$conn->escape_string(stripslashes($string)) : $string;
    }

}
