<?php
/**
 * PDO Mysql Adapter. The default and preferred DB adapter to use.
 * User: Alf Magne
 * Date: 31.01.13

 */
class LudoDBPDO extends LudoDB implements LudoDBAdapter
{
    /**
     * @var PDO
     */
    protected static $conn;

    public function connect()
    {
        try{
            self::$conn = new PDO("mysql:host=".self::getHost().";dbname=".self::getDb(), self::getUser(), self::getPassword());
        }catch(PDOException $e){
            throw new LudoDBConnectionException("Could not connect to database because ". $e->getMessage(), 400);
        }
    }

    public function escapeString($string)
    {
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
        if ($this->debug) $this->log($sql, $params);
        if (self::$loggingEnabled) {
            self::$queryCounter++;
        }
        if(!is_array($params))$params = array($params);
        $stmt = self::$conn->prepare($sql);
        if(!$stmt->execute($params)){
            throw new Exception("Invalid PDO query ". $sql . " (". implode(",", $params).")");
        }
        return $stmt;
    }

    /**
     * @param $sql
     * @param array $params
     * @return array|null
     */
    public function one($sql, $params = array())
    {
        $res = $this->query($sql . " limit 1", $params);
        $row = $res->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    /**
     * @param $sql
     * @param array $params
     * @return int
     */
    public function countRows($sql, $params = array())
    {
        $res = $this->query($sql, $params);
        return $res->rowCount();
    }

    /**
     * Get last insert id
     * @method getInsertId
     * @return int
     */
    public function getInsertId()
    {
        return self::$conn->lastInsertId();
    }

    /**
     * @param mysqli_result|resource|PDOStatement $result
     * @return array
     */
    public function nextRow($result)
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $sql
     * @param array $params
     * @return null|array
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql . " limit 1", $params);
        $row = $result->fetch(PDO::FETCH_NUM);
        return (isset($row)) ? $row[0] : null;
    }
}
