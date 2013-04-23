<?php
/**
 * User: Alf Magne
 * Date: 01.02.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * MysqlI adapter. PDO is the preferred adapter and should be used
 * when supported by the server.
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBMySqlI extends LudoDB implements LudoDBAdapter
{
    /**
     * Database connection resource reference
     * @var mysqli
     */
    protected static $conn;

    /**
     * Connect to database
     * @return mixed|void
     * @throws LudoDBConnectionException
     */
    public function connect()
    {

        ob_start();
        self::$conn = new mysqli(self::getHost(), self::getUser(), self::getPassword(), self::getDb());
        ob_end_clean();
        if (self::$conn->connect_errno) {
            throw new LudoDBConnectionException("Could not connect to database because: " . self::$conn->connect_error, 400);
        }
    }



    /**
     * Execute query and return resource.
     * @param $sql
     * @param array $params
     * @return bool|mysqli_result
     * @throws Exception
     */
    public function query($sql, $params = array())
    {
        if (self::$logSQLs) $this->log($sql);
        if (self::$loggingEnabled) {
            self::$queryCounter++;
        }
        if (!empty($params)) {
            $sql = LudoDBSql::fromPrepared($sql, $params);
        }
        if ($res = self::$conn->query($sql)) {
            return $res;
        }
        throw new Exception("SQL ERROR: " . self::$conn->error . "(" . $sql . ")");
    }

    /**
     * Get one row
     * @param $sql
     * @param array $params
     * @return array|null
     */
    public function one($sql, $params = array())
    {
        if (self::$logSQLs) $this->log($sql);
        $res = $this->query($sql . " limit 1", $params);
        if ($res && $row = $res->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    /**
     * Return number of rows for given query with given arguments.
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
     * @return int
     */
    public function getInsertId()
    {
        return self::$conn->insert_id;
    }

    /**
     * Return reference to next row in result set.
     * @param mysqli_result|resource|PDOStatement $result
     * @return array
     */
    public function nextRow($result)
    {
        return $result->fetch_assoc();
    }

    /**
     * Return value of first column of first row.
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

    /**
     * Return safe string for insertion into database.
     * @param $string
     * @return mixed|string
     */
    public function escapeString($string)
    {
        return is_string($string) ? self::$conn->escape_string(stripslashes($string)) : $string;
    }
    /**
     * Return table definition, column names and column types for a table.
     * @param String $tableName
     * @return array
     */
    public function getTableDefinition($tableName){
        return array();
    }

}
