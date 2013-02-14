<?php
/**
 * Base class for LudoDB models and collections.
 * User: Alf Magne Kalleland
 * Date: 20.12.12

 */
abstract class LudoDBObject
{
    /**
     * @var LudoDB
     */
    protected $db;
    protected $arguments;
    protected static $configParsers = array();

    /**
     * Valid public services offered by this class, example "read", "delete" and "save"
     * @return array
     */
    public function getValidServices()
    {
        return array();
    }

    /**
     * True when config is in JSONConfig/<class name>.json file
     * @var bool
     */
    protected $JSONConfig = false;

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
            $this->arguments = $this->escapeArguments(func_get_args());
        }
        $this->parser = $this->configParser();
        $this->onConstruct();
    }

    protected function sqlHandler()
    {
        if (!isset($this->sql_handler)) {
            $this->sql_handler = new LudoDBSql($this);
        }
        return $this->sql_handler;
    }

    protected function escapeArguments($values)
    {
        $ret = array();
        foreach ($values as $value) {
            if(isset($value))$ret[] = $this->db->escapeString($value);
        }
        return $ret;
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
        return $this->arguments;
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
        return json_encode($this->getValues());
    }

    public function cacheEnabled()
    {
        return false;
    }

    abstract public function getValues();

    private $cacheKey = null;

    public function getCacheKey()
    {
        if (!isset($this->cacheKey)) {
            if (isset($this->arguments) && count($this->arguments)) {
                $this->cacheKey = get_class($this) . "_" . implode("_", $this->arguments);
            }
        }
        return $this->cacheKey;
    }

    protected function clearCache()
    {
        if ($this->cacheEnabled()) {
            LudoDBCache::clearBy($this->getCacheKey());
        }
    }

    public function read()
    {
        return $this->getValues();
    }

    /**
     * @param array $columnsEqual
     * Returns true if database table has rows where one of the given columns has one of the
     * given values,
     * @example
            if($this->hasRowWith(array("email" => "name@dhtmlgoodies.com"));
     */
    public function hasRowWith(array $columnsEqual){
        $sql= "select * from ". $this->parser->getTableName()." where ";
        $sql.= implode("=? or ", array_keys($columnsEqual));
        $sql.= "=?";
        $row = $this->db->one($sql, array_values($columnsEqual));
        if(isset($row)){
            return true;
        }
        return false;
    }

}
