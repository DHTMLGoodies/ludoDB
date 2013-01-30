<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * Time: 14:31
 */
abstract class LudoDBObject
{
    /**
     * @var LudoDB
     */
    protected $db;
    protected $constructorValues;
    protected static $configParsers = array();
    /**
     * True when config is in JSONConfig/<class name>.json file
     * @var bool
     */
    protected $JSONConfig = false;
    /**
     * True to enable JSON caching
     * @var bool
     */
    protected $JSONCaching = false;

    private $sql_handler;
    protected $config;

    /**
     * @var LudoDBCollectionConfigParser|LudoDBConfigParser
     */
    protected $parser;

    public function __construct()
    {
        $this->db = LudoDb::getInstance();
        if (func_num_args() > 0) {
            $this->constructorValues = $this->getValidConstructorValues(func_get_args());
        }
        $this->parser = $this->configParser();
        $this->onConstruct();
    }

    protected function sqlHandler()
    {
        if (!isset($this->sql_handler)) {
            $this->sql_handler = new LudoSQL($this);
        }
        return $this->sql_handler;
    }


    protected function getValidConstructorValues($values)
    {
        foreach ($values as &$value) {
            $value = $this->db->escapeString($value);
        }
        return $values;
    }

    protected function onConstruct()
    {

    }


    public function hasConfigInExternalFile()
    {
        return $this->JSONConfig;
    }

    public function getConstructorValues()
    {
        return $this->constructorValues;
    }

    public function commit()
    {

    }

    /**
     * @return LudoDBConfigParser|LudoDBCollectionConfigParser
     */
    public function configParser()
    {
        if (!isset($this->parser)) {
            $key = $this->getConfigParserKey();
            if (!isset(self::$configParsers[$key])) {
                self::$configParsers[$key] = $this->getConfigParserInstance();
            }
            $this->parser = self::$configParsers[$key];
        }
        return $this->parser;
    }

    protected function getConfigParserInstance()
    {
        return new LudoDBConfigParser($this, isset($this->config) ? $this->config : array());
    }

    private $configParserKey;

    protected function getConfigParserKey()
    {
        if (!isset($this->configParserKey)) {
            $this->configParserKey = get_class($this);
        }
        return $this->configParserKey;
    }

    public static function clearParsers()
    {
        self::$configParsers = array();
    }

    public function getUncommitted()
    {
        return array();
    }

    public function getId()
    {
        return null;
    }

    public function __toString()
    {
        return $this->asJSON();
    }

    /**
     * @return string
     */
    public function asJSON()
    {
        return $this->JSONHandler()->asJSON();
    }

    public function JSONCacheEnabled(){
        return $this->JSONCaching;
    }

    private $_jsonHandler;
    private function JSONHandler(){
        if(!isset($this->_jsonHandler)){
            $this->_jsonHandler = new LudoJSONHandler($this);
        }
        return $this->_jsonHandler;
    }

    abstract public function getValues();

    private $JSONKey = null;
    public function getJSONKey(){
        if(!isset($this->JSONKey)){
            if(isset($this->constructorValues) && count($this->constructorValues)){
                $this->JSONKey = get_class($this)."_".implode("_", $this->constructorValues);
            }
        }
        return $this->JSONKey;
    }

    protected function clearCache(){
        if($this->JSONCaching){
            LudoDBCache::clearCacheBy($this->getJSONKey());
            $this->JSONHandler()->clearCacheObject();
        }
    }
}
