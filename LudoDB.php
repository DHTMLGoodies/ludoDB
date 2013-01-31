<?php
/**
 * MySql DB layer
 * User: Alf Magne Kalleland
 * Date: 03.11.12
 * Time: 01:37
 */
class LudoDB
{
    private $debug = false;
    private static $_useMysqlI;
    private static $instance;
    private static $loggingEnabled = false;
    private static $startTime;
    private static $queryCounter = 0;
    private static $connectionType = 'PDO'; // PDO|MySqlI

    /**
     * @var PDO
     */
    private static $PDO = null;
    /**
     * @var mysqli
     */
    private static $conn = null;
    

    public function __construct()
    {
        if (self::$loggingEnabled) {
            self::$startTime = self::getTime();
        }
        $this->connect();
    }

    /**
     * Set connection type,  PDO|MySqlI|MySql
     * @param $type
     */
    public static function setConnectionType($type = 'PDO')
    {
        self::$connectionType = $type;
        self::$PDO = null;
        self::$conn = null;
        self::getInstance()->connect();
    }

    public static function mySqlI(){
        self::setConnectionType('MySqlI');
    }

    public static function hasPDO(){
        return self::$connectionType === 'PDO';
    }

    public static function enableLogging()
    {
        self::$loggingEnabled = true;
        if (!isset(self::$startTime)) self::$startTime = self::getTime();
    }

    public static function isLoggingEnabled()
    {
        return self::$loggingEnabled;
    }

    public static function getElapsed()
    {
        return self::getTime() - self::$startTime;
    }

    public static function getQueryCount()
    {
        return self::$queryCounter;
    }

    private static function getTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function getInstance($useMysqlI = true)
    {
        if (!isset(self::$instance)) {
            self::$instance = new LudoDB($useMysqlI);
        }
        return self::$instance;
    }

    public static function setHost($host)
    {
        LudoDBRegistry::set('DB_HOST', $host);
    }

    public static function setUser($user)
    {
        LudoDBRegistry::set('DB_USER', $user);
    }

    public static function setPassword($pwd)
    {
        LudoDBRegistry::set('DB_PWD', $pwd);
    }

    public static function setDb($dbName)
    {
        LudoDBRegistry::set('DB_NAME', $dbName);
    }

    private function connect()
    {
        $host = LudoDBRegistry::get('DB_HOST');
        $user = LudoDBRegistry::get('DB_USER');
        $pwd = LudoDBRegistry::get('DB_PWD');
        $db = LudoDBRegistry::get('DB_NAME');

        if (self::$connectionType==='PDO') {
            self::$PDO = new PDO("mysql:host=$host;dbname=$db", $user, $pwd);
        } else if (self::$connectionType == 'MySqlI') {
            self::$conn = new mysqli($host, $user, $pwd, $db);
        } else {
            self::$conn = mysql_connect($host, $user, $pwd);
            mysql_select_db($db, self::$conn);
        }
    }

    public function escapeString($string)
    {
        if (self::$PDO) {
            return $string;
        }
        if (is_string($string)) {
            $string = stripslashes($string);
            if (self::$_useMysqlI) return self::$conn->escape_string($string);
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
        if (self::$connectionType == 'PDO') {
            if(!is_array($params))$params = array($params);
            $stmt = self::$PDO->prepare($sql);
            if(!$stmt->execute($params)){
                throw new Exception("Invalid PDO query ". $sql . " (". implode(",", $params));
            }
            return $stmt;
        }else{
            if(!empty($params)){
                $sql = LudoSQL::fromPrepared($sql, $params);
            }
        }
        if (self::$connectionType == 'MySqlI') {
            if ($res = self::$conn->query($sql)) {
                return $res;
            }
            throw new Exception("SQL ERROR: " . self::$conn->error . "(" . $sql . ")");

        }
        $res = mysql_query($sql) or die(mysql_error() . "\nSQL:" . $sql);
        return $res;
    }

    /**
     * @param $sql
     * @return array|null
     */
    public function one($sql, $params = array())
    {
        if ($this->debug) $this->log($sql);
        $res = $this->query($sql . " limit 1", $params);
        if (self::$connectionType == 'PDO') {
            $row = $res->fetch(PDO::FETCH_ASSOC);
            return $row ? $row : null;
        } else if (self::$connectionType == 'MySqlI') {
            if ($res && $row = $res->fetch_assoc()) {
                return $row;
            }
        } else {
            if ($row = mysql_fetch_assoc($res)) {
                return $row;
            }
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
        if (self::$PDO) {
            return $res->rowCount();
        } else if (self::$conn) {
            return ($res) ? $res->num_rows : 0;
        }
        return mysql_num_rows($res);
    }

    /**
     * Get last insert id
     * @method getInsertId
     * @return int
     */
    public function getInsertId()
    {
        if (self::$PDO) return self::$PDO->lastInsertId();
        if (self::$_useMysqlI) return self::$conn->insert_id;
        return mysql_insert_id();
    }

    /**
     * @param mysqli_result|resource|PDOStatement $result
     * @return array
     */
    public function nextRow($result)
    {
        if(self::$PDO){
            return $result->fetch(PDO::FETCH_ASSOC);
        }
        if (self::$_useMysqlI) {
            return $result->fetch_assoc();
        }
        return mysql_fetch_assoc($result);
    }

    /**
     * @param $sql
     * @return null|array
     */
    public function getValue($sql, $params = array())
    {
        $result = $this->query($sql . " limit 1", $params);
        if(self::$PDO){
            $row = $result->fetch(PDO::FETCH_NUM);
        }
        else if (self::$_useMysqlI) {
            $row = $result->fetch_row();
        } else {
            $row = mysql_fetch_row($result);
        }
        if (isset($row)) return $row[0];
        return null;
    }

    public function tableExists($tableName)
    {
        return $this->countRows("show tables like ?", array($tableName)) > 0;
    }

    public function log($sql)
    {
        $fh = fopen("sql.txt", "a+");
        fwrite($fh, $sql . "\n");
        fclose($fh);
    }
}