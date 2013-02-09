<?php
/**
 * Base class for LudoDB adapters
 * User: Alf Magne Kalleland
 * Date: 03.11.12
 */
class LudoDB
{
    protected $debug = false;
    private static $instance;
    protected static $loggingEnabled = false;
    protected static $startTime;
    protected static $queryCounter = 0;
    private static $connectionType = 'PDO'; // PDO|MYSQLI
    protected static $conn;

    public function __construct()
    {
        if (self::$loggingEnabled) {
            self::$startTime = self::getTime();
        }
    }

    /**
     * Set connection type,  PDO|MySqlI|MySql
     * @param $type
     */
    public static function setConnectionType($type = 'PDO')
    {
        if($type != self::$connectionType){
            self::$conn = null;
            self::$instance = null;
            self::$connectionType = $type;
            self::getInstance($type);
        }
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

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            switch(self::$connectionType){
                case 'PDO':
                    self::$instance = new LudoDBPDO();
                    break;
                case 'MYSQLI':
                    self::$instance = new LudoDBMySqlI();
                    break;
                default:
                    self::$instance = new LudoDBMySql();
            }
            self::$instance->connect();
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

    protected static function getHost(){
        return LudoDBRegistry::get('DB_HOST');
    }

    protected static function getUser(){
        return LudoDBRegistry::get('DB_USER');
    }

    protected static function getPassword(){
        return LudoDBRegistry::get('DB_PWD');
    }

    public static function getDb(){
        return LudoDBRegistry::get('DB_NAME');
    }

    public function tableExists($tableName)
    {
        return $this->countRows("show tables like ?", array($tableName)) > 0;
    }

    public function countRows(){
        return 0;
    }

    public static function createDatabase($name){
        $name = preg_replace("/[^0-9a-z_]/si", "", $name);
        self::getInstance()->query("create database if not exists ".$name);
    }

    public function useDatabase($name){
        if($this->databaseExists($name)){
            self::getInstance()->query("use ". $name);
        }
    }

    public function databaseExists($name){
        return $this->countRows("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", array($name)) > 0;
    }


    public function log($sql, $arguments = array())
    {
        $fh = fopen("sql.txt", "a+");
        $logText = $sql;
        if(!empty($arguments)){
            if(!is_array($arguments))$arguments = array($arguments);
            $logText.= ", arguments: (". implode(",", $arguments). ")";
        }else{
            $logText.= " no arguments";
        }
        fwrite($fh, $logText . "\n");
        fclose($fh);
    }
}