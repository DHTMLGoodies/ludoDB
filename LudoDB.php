<?php
/**
 * MySql DB layer
 * User: Alf Magne Kalleland
 * Date: 03.11.12
 * Time: 01:37
 */
class LudoDB
{
    private $debug = true;
    private static $mysqli;
    const DELETED = '__DELETED__';
    private static $instance;

    /**
     * @var mysqli
     */
    private static $conn;

    public function __construct($useMysqlI = true)
    {
        if (!isset(self::$mysqli)) {
            if (!$useMysqlI) self::$mysqli = false;
            else self::$mysqli = class_exists("mysqli");
        }
        if (!isset(self::$conn)) {
            $this->connect();
        }
    }

    public static function getInstance($useMysqlI = true)
    {
        if (!isset(self::$instance)) {
            self::$instance = new LudoDB($useMysqlI);
        }
        return self::$instance;
    }

    public static function disableMySqli()
    {

        if (isset(self::$mysqli) && self::$mysqli) {
            self::$mysqli = false;
            if (isset(self::$conn)) {
                self::$conn = null;
                self::getInstance()->connect();
            }
        }
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

        if (self::$mysqli) {
            self::$conn = new mysqli($host, $user, $pwd, $db);
        } else {
            self::$conn = mysql_connect($host, $user, $pwd);
            mysql_select_db($db, self::$conn);
        }
    }

    public function escapeString($string){
        if(is_string($string)){
            $string = stripslashes($string);
            if(self::$mysqli)return self::$conn->escape_string($string);
            return mysql_real_escape_string($string);
        }
        return $string;
    }

    /**
     * @param $sql
     * @return bool|mysqli_result|resource
     */
    public function query($sql)
    {
        if ($this->debug) $this->log($sql);
        if (self::$mysqli) {
            if($res = self::$conn->query($sql)){
                return $res;
            }
            die(self::$conn->error);

        }
        $res = mysql_query($sql) or die(mysql_error() . "\nSQL:" . $sql);
        return $res;
    }

    /**
     * @param $sql
     * @return array|null
     */
    public function one($sql)
    {
        if ($this->debug) $this->log($sql);
        $res = $this->query($sql . " limit 1");
        if (self::$mysqli) {
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
     * Return number of rows in query
     * @method countRows
     * @param String $sql
     * @return int
     */
    public function countRows($sql)
    {
        if ($this->debug) $this->log($sql);
        if (self::$mysqli) {
            $res = $this->query($sql);
            return ($res) ? $res->num_rows : 0;
        }
        return mysql_num_rows($this->query($sql));
    }

    /**
     * Get last insert id
     * @method getInsertId
     * @return int
     */
    public function getInsertId()
    {
        if (self::$mysqli) return self::$conn->insert_id;
        return mysql_insert_id();
    }

    /**
     * @param mysqli_result|resource
     * @return array
     */
    public function nextRow($result)
    {
        if (self::$mysqli) {
            return $result->fetch_assoc();
        }
        return mysql_fetch_assoc($result);
    }

    /**
     * @param $sql
     * @return null|array
     */
    public function getValue($sql)
    {
        if ($this->debug) $this->log($sql);
        $result = $this->query($sql . " limit 1");
        if (self::$mysqli) {
            $row = $result->fetch_row();
        } else {
            $row = mysql_fetch_row($result);
        }
        if (isset($row)) return $row[0];
        return null;
    }

    public function tableExists($tableName)
    {
        return $this->countRows("show tables like '" . $tableName . "'") > 0;
    }

    public function log($sql)
    {
        $fh = fopen("sql.txt", "a+");
        fwrite($fh, $sql . "\n");
        fclose($fh);
    }

    public function insert(LudoDBTable $obj)
    {
        $table = $obj->configParser()->getTableName();
        $data = $obj->getUpdates();
        if (!isset($data)) $data = array(
            $obj->configParser()->getIdField() => NULL
        );

        $this->mysql_insert($table, $data);

    }

    private function mysql_insert($table, $data)
    {
        $sql = "insert into " . $table;
        $sql .= "(" . implode(",", array_keys($data)) . ")";
        $sql .= "values('" . implode("','", array_values($data)) . "')";
        $this->query($sql);
    }

    public function update(LudoDBTable $obj)
    {
        if (self::$mysqli) {
            $this->mysqli_update($obj);
        } else {
            $this->mysql_update($obj);
        }
    }

    private function mysqli_update(LudoDBTable $obj)
    {
        return $this->mysql_update($obj);
    }

    private function mysql_update(LudoDBTable $obj)
    {
        return $this->query("update " . $obj->configParser()->getTableName() . " set " . $this->getUpdatesForSql($obj->getUpdates()) . " where " . $obj->configParser()->getIdField() . " = '" . $obj->getId() . "'");
    }

    private function getUpdatesForSql($updates)
    {
        $ret = array();
        foreach ($updates as $key => $value) {
            $ret[] = $key . "=" . ($value === self::DELETED ? 'NULL' : "'" . $value . "'");
        }
        return implode(",", $ret);
    }
}