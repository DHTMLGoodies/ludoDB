<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 31.01.13
 * Time: 19:02
 * To change this template use File | Settings | File Templates.
 */
class LudoDBPDO extends LudoDB implements LudoDBAdapter
{
    private static $conn;
    
    protected function connect()
    {
        $host = LudoDBRegistry::get('DB_HOST');
        $user = LudoDBRegistry::get('DB_USER');
        $pwd = LudoDBRegistry::get('DB_PWD');
        $db = LudoDBRegistry::get('DB_NAME');

        self::$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pwd);
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
        if ($this->debug) $this->log($sql);
        if (self::$loggingEnabled) {
            self::$queryCounter++;
        }
        if(!is_array($params))$params = array($params);
        $stmt = self::$conn->prepare($sql);
        if(!$stmt->execute($params)){
            throw new Exception("Invalid PDO query ". $sql . " (". implode(",", $params));
        }
        return $stmt;
    }

    /**
     * @param $sql
     * @return array|null
     */
    public function one($sql, $params = array())
    {
        if ($this->debug) $this->log($sql);
        $res = $this->query($sql . " limit 1", $params);
        $row = $res->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    /**
     * @param $sql
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
     * @return null|array
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql . " limit 1", $params);
        $row = $result->fetch(PDO::FETCH_NUM);
        if (isset($row)) return $row[0];
        return null;
    }
}
