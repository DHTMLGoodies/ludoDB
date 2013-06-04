<?php
/**
 * Base class for LudoDB adapters
 * User: Alf Magne Kalleland
 * Date: 03.11.12
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * LudoDB class
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDB
{

    const ADAPTER_PDO = 'PDO';
    const ADAPTER_MYSQLI = 'MYSQLI';
    const ADAPTER_MYSQL = 'MYSQL';
    const ADAPTER_PDO_ORACLE = 'PDO_ORACLE';

    /**
     * True when logging SQL's to file
     * @var bool
     */
    protected static $logSQLs = false;
    /**
     * LudoDB references
     * @var LudoDB
     */
    private static $instance;
    /**
     * Return logging details in response from LudoDBRequestHandler
     * @var bool
     */
    protected static $loggingEnabled = false;
    /**
     * Internal microtime representing start time for LudoDB operations.
     * @var float
     */
    protected static $startTime;
    /**
     * Number of SQL queries executed
     * @var int
     */
    protected static $queryCounter = 0;
    /**
     * Connection type
     * @var string
     */
    private static $connectionType = 'PDO'; // PDO|MYSQLI

    /**
     * Connection object
     * @var PDO
     */
    protected static $conn;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (self::$loggingEnabled && !isset(self::$startTime)) {
            self::$startTime = self::getTime();
        }
    }


    /**
     * Set connection type,  PDO|MySqlI|MySql
     * @param $type
     */
    public static function setConnectionType($type = self::ADAPTER_PDO)
    {
        if($type != self::$connectionType){
            self::$conn = null;
            self::$instance = null;
            self::$connectionType = $type;
            self::getInstance($type);
        }
    }

    /**
     * Logs all sql queries to sql.txt
     */
    public static function enableSqlLogging(){
        self::$logSQLs = true;
    }

    /**
     * Returns true if connection type is PDO
     * @return bool
     */
    public static function hasPDO(){
        return self::$connectionType === self::ADAPTER_PDO;
    }

    /**
     * Used to enable logging details(elapsed time and number of SQL queries) in JSON response from LudoDBRequestHandler
     */
    public static function enableLogging()
    {
        self::$loggingEnabled = true;
        if (!isset(self::$startTime)) self::$startTime = self::getTime();
    }

    /**
     * Returns true if logging of SQL queries and elapsed time in LudoDBRequestHandler
     * @return bool
     */
    public static function isLoggingEnabled()
    {
        return self::$loggingEnabled;
    }

    /**
     * Return elapsed time for LudoDB operations.
     * @return mixed
     */
    public static function getElapsed()
    {
        return self::getTime() - self::$startTime;
    }

    /**
     * Return number of executed SQL queries.
     * @return int
     */
    public static function getQueryCount()
    {
        return self::$queryCounter;
    }

    /**
     * Return microtime
     * @return mixed
     */
    private static function getTime()
    {
        return microtime(true);
    }

    /**
     * Return new LudoDB object with connection to the database.
     * @return LudoDB|LudoDBMySql|LudoDBMySqlI|LudoDBPDO
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            switch(self::$connectionType){
                case self::ADAPTER_PDO:
                    self::$instance = new LudoDBPDO();
                    break;
                case self::ADAPTER_MYSQLI:
                    self::$instance = new LudoDBMySqlI();
                    break;
                case self::ADAPTER_PDO_ORACLE:
                    self::$instance = new LudoDBPDOOracle();
                    break;
                default:
                    self::$instance = new LudoDBMySql();
            }
            self::$instance->connect();
        }
        return self::$instance;
    }

    /**
     * Set host for database connection.
     * @param $host
     */
    public static function setHost($host)
    {
        LudoDBRegistry::set('DB_HOST', $host);
    }

    /**
     * Set user name for database connection
     * @param $user
     */
    public static function setUser($user)
    {
        LudoDBRegistry::set('DB_USER', $user);
    }

    /**
     * Set password for database connection.
     * @param $pwd
     */
    public static function setPassword($pwd)
    {
        LudoDBRegistry::set('DB_PWD', $pwd);
    }

    /**
     * Set name of database used in database connection.
     * @param $dbName
     */
    public static function setDb($dbName)
    {
        LudoDBRegistry::set('DB_NAME', $dbName);
    }

    /**
     * Return specified host for database connection.
     * @return String
     */
    protected static function getHost(){
        return LudoDBRegistry::get('DB_HOST');
    }

    /**
     * Return username for database connection.
     * @return String
     */
    protected static function getUser(){
        return LudoDBRegistry::get('DB_USER');
    }
    /**
     * Return password for database connection.
     * @return String
     */
    protected static function getPassword(){
        return LudoDBRegistry::get('DB_PWD');
    }

    /**
     * Return name of database for database connection.
     * @return String
     */
    public static function getDb(){
        return LudoDBRegistry::get('DB_NAME');
    }

    /**
     * Returns true if given database table exists.
     * @param $tableName
     * @return String
     */
    public function tableExists($tableName)
    {
        return $this->countRows("show tables like ?", array($tableName)) > 0;
    }

    /**
     * countRows is implemented in sub classes.
     * @return int
     */
    protected function countRows(){
        return 0;
    }

    /**
     * Create database with given name
     * @param $name
     */
    public static function createDatabase($name){
        $name = preg_replace("/[^0-9a-z_]/si", "", $name);
        self::getInstance()->query("create database if not exists ".$name);
    }

    /**
     * Use this databsae.
     * @param $name
     */
    public function useDatabase($name){
        if($this->databaseExists($name)){
            self::getInstance()->query("use ". $name);
        }
    }

    /**
     * Returns true if database with given name exists.
     * Example:
     * <code>
     *
     * $instance =
     * if(!LudoDB::getInstance()->databaseExists('nameOfDatabase')){
     *      LudoDB::getInstance()->createDatabase('nameOfDatabase');
     * }
     *
     * </code>
     * @param $name
     * @return bool
     */
    public function databaseExists($name){
        return $this->countRows("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", array($name)) > 0;
    }

    /**
     * Log SQL - this method is mostly used internally.
     * @param $sql
     * @param array $arguments
     */
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

    /**
     * Returns true if we have a database connection.
     * Example:
     * <code>
     *
     * LudoDB::hasConnection()
     *
     * </code>
     * @return bool
     */
    public static function hasConnection(){
        try{
            self::getInstance()->connect();
            return true;
        }catch(LudoDBConnectionException $e){
            return false;
        }
    }

    protected function getSqlForTableDef($tableName){
        return "SHOW COLUMNS FROM ".$tableName;
    }
}