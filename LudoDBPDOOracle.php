<?php
/**
 * User: Alf Magne Kalleland
 * Date: 24.04.13
 * Time: 00:01
 * @package LudoDB
 */

/**
 * LudoDB PDO adapter for Oracle database connections.
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBPDOOracle extends LudoDB implements LudoDBAdapter
{
    /**
     * Database connection resource reference
     * @var PDO
     */
    protected static $conn;

    /**
     *
     * Connect to database
     * @throws LudoDBConnectionException
     */
    public function connect()
    {
        try {
            $connectionString = "dbname=//" . self::getHost() . ":" . self::getPort() . "/" . self::getSID() . "/" . self::getInstanceName();
            self::$conn = new PDO($connectionString, self::getUser(), self::getPassword());
        } catch (PDOException $e) {
            throw new LudoDBConnectionException("Could not connect to database because " . $e->getMessage(), 400);
        }
    }

    /**
     * Return port of Oracle conection.
     * @return String
     */
    public function getPort()
    {
        return LudoDBRegistry::get('DB_PORT');
    }

    /**
     * Set port for Oracle connection.
     * @param int $port
     */
    public function setPort($port)
    {
        LudoDBRegistry::set('DB_PORT', $port);
    }

    /**
     * Return SID for Oracle connection
     * @return String
     */
    public function getSID()
    {
        return LudoDBRegistry::get('DB_SID');
    }

    /**
     * Set SID for oracle connection
     * @param String
     */
    public function setSID($sid)
    {
        LudoDBRegistry::set('DB_SID', $sid);
    }

    /**
     * Return instance name for Oracle connectoin
     * @return String
     */
    public function getInstanceName()
    {
        return LudoDBRegistry::get('DB_INSTANCE');
    }

    /**
     * Set instance name for Oracle connection
     * @param $instance
     */
    public function setInstanceName($instance)
    {
        LudoDBRegistry::set('DB_INSTANCE', $instance);
    }

    /**
     * Escape string - nothing to do here since we're using prepared statements.
     * @param $string
     * @return mixed
     */
    public function escapeString($string)
    {
        return $string;
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
        if (self::$logSQLs) $this->log($sql, $params);
        if (self::$loggingEnabled) {
            self::$queryCounter++;
        }
        if (!is_array($params)) $params = array($params);
        $stmt = self::$conn->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new Exception("Invalid PDO query " . $sql . " (" . implode(",", $params) . ")");
        }
        return $stmt;
    }

    /**
     * Get one row.
     * @param $sql
     * @param array $params
     * @return array|null
     */
    public function one($sql, $params = array())
    {
        $res = $this->query($sql, $params);
        $row = $res->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
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
        return $res->rowCount();
    }

    /**
     * Get last insert id
     * @return int
     */
    public function getInsertId()
    {
        return self::$conn->lastInsertId();
    }

    /**
     * Go to next row
     * @param mysqli_result|resource|PDOStatement $result
     * @return array
     */
    public function nextRow($result)
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Return value of first column in a query
     * @param $sql
     * @param array $params
     * @return null|array
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql, $params);
        $row = $result->fetch(PDO::FETCH_NUM);
        return (isset($row)) ? $row[0] : null;
    }

    /**
     * Return table definition, column names and column types for a table.
     * @param String $tableName
     * @return array
     */
    public function getTableDefinition($tableName){
        $res = $this->query($this->getSqlForTableDef($tableName));
        $ret = array();
        while($row = $this->nextRow($res)){
            $ret[$row['Field']] = $row['Type'];
        }
        return $ret;
    }

    protected function getSqlForTableDef()
    {
        return "SELECT column_name FROM user_tab_cols WHERE table_name = ?";
    }
}
