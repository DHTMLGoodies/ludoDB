<?php
/**
 * User: Alf Magne
 * Date: 01.02.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Mysql adapter. Should only be used when neither
 * PDO or MySqlI is supported on the server.
 * @package LudoDB
 */
class LudoDBMysql extends LudoDB implements LudoDBAdapter
{
    /**
     * Connect to database
     * @throws LudoDBConnectionException
     */
    public function connect()
    {
        ob_start();
        self::$conn = mysql_connect(self::getHost(), self::getUser(), self::getPassword());
        if (!self::$conn) {
            throw new LudoDBConnectionException("Could not connect to database because: " . mysql_error(), 400);
        }
        mysql_select_db(self::getDb(), self::$conn);
    }

    /**
     * Return safe string for insertion into database.
     * @param int|string|bool|null $string
     * @return int|string|bool|null
     */
    public function escapeString($string)
    {
        return is_string($string) ? mysql_real_escape_string(stripslashes($string)) : $string;
    }

    /**
     * Execute query and return resource.
     * @param $sql
     * @param array $params
     * @return bool|mysqli_result|resource|PDOStatement
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
        $res = mysql_query($sql) or die(mysql_error() . "\nSQL:" . $sql);
        return $res;
    }

    /**
     * Get one row.
     * @param $sql
     * @param array $params
     * @return array|null
     */
    public function one($sql, $params = array())
    {
        if (self::$logSQLs) $this->log($sql);
        $res = $this->query($sql . " limit 1", $params);
        if ($row = mysql_fetch_assoc($res)) {
            return $row;
        }
        return null;
    }

    /**
     * Return number of rows.
     * @param $sql
     * @param array $params
     * @return int
     */
    public function countRows($sql, $params = array())
    {
        $res = $this->query($sql, $params);
        return mysql_num_rows($res);
    }

    /**
     * Get last insert id
     * @return int
     */
    public function getInsertId()
    {
        return mysql_insert_id();
    }

    /**
     * Go to next row
     * @param mysqli_result|resource|PDOStatement $result
     * @return array
     */
    public function nextRow($result)
    {
        return mysql_fetch_assoc($result);
    }

    /**
     * Return value of first column in a query
     * @param $sql
     * @param array $params
     * @return null|array
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql . " limit 1", $params);
        $row = mysql_fetch_row($result);
        return (isset($row)) ? $row[0] : null;
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
