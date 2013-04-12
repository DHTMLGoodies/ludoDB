<?php
/**
 * Base class for LudoDB models and collections.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Base class for LudoDBModel and LudoDBCollection
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
abstract class LudoDBObject
{
    /**
     * Internal LudoDB instance.
     * @var LudoDB
     */
    protected $db;
    /**
     * Constructor arguments
     * @var array
     */
    protected $arguments;
    /**
     * Internal cache of config parsers for LudoDBObjects.
     * @var array
     */
    protected static $configParsers = array();

    /**
     * True when config is in JSONConfig/<class name>.json file
     * @var bool
     */
    protected $JSONConfig = false;

    /**
     * Internal reference to LudoDBSql
     * @var LudoDBSql
     */
    private $sql_handler;

    /**
     * LudoDB config
     * @example examples/cities/DemoCity.php
     * @var array
     */
    protected $config;

    /**
     * Valid public services offered by this class, example "read", "delete" and "save"
     * @return array
     */
    public function getValidServices()
    {
        return array();
    }

    /**
     * LudoDBService getOnSuccessMessageFor method. By default, it returns an empty string.
     * @param $service
     * @return string
     */
    public function getOnSuccessMessageFor($service){
        return "";
    }

    /**
     * Internal reference to config parser
     * @var LudoDBCollectionConfigParser|LudoDBConfigParser
     */
    protected $parser;

    /**
     * Constructs a new LudoDBModel/LudoDBCollection
     */
    public function __construct()
    {
        $this->db = LudoDb::getInstance();
        if (func_num_args() > 0) {
            $this->arguments = $this->escapeArguments(func_get_args());
        }
        $this->parser = $this->configParser();
        $this->onConstruct();
    }

    /**
     * Return SQL handler
     * @return LudoDBSql
     */
    protected function sqlHandler()
    {
        if (!isset($this->sql_handler)) {
            $this->sql_handler = new LudoDBSql($this);
        }
        return $this->sql_handler;
    }

    /**
     * Escape constructor arguments.
     * @param $values
     * @return array
     */
    protected function escapeArguments($values)
    {
        $ret = array();
        foreach ($values as $value) {
            if(isset($value))$ret[] = $this->db->escapeString($value);
        }
        return $ret;
    }

    /**
     * On construct method which can be implemented by sub classes.
     */
    protected function onConstruct()
    {

    }

    /**
     * Returns true if config is defined in external file.
     * @return bool
     */
    public function hasConfigInExternalFile()
    {
        return $this->JSONConfig;
    }

    /**
     * Return array of values sent to constructor.
     * @return array
     */
    public function getConstructorValues()
    {
        return $this->arguments;
    }

    /**
     * Commit method implemented by sub classes.
     */
    public function commit()
    {

    }

    /**
     * Return reference to config parser.
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

    /**
     * Return config parser instance.
     * @return LudoDBConfigParser
     */
    protected function getConfigParserInstance()
    {
        return new LudoDBConfigParser($this, isset($this->config) ? $this->config : array());
    }

    /**
     * The key of this class in the static $configParsers cache array.
     * @var string
     */
    private $configParserKey;

    /**
     * Return config parser key of this class.
     * @return string
     */
    protected function getConfigParserKey()
    {
        if (!isset($this->configParserKey)) {
            $this->configParserKey = get_class($this);
        }
        return $this->configParserKey;
    }

    /**
     * Clear all cached config parsers
     */
    public static function clearParsers()
    {
        self::$configParsers = array();
    }

    /**
     * Return uncommitted data. This method is implemented in LudoDBModel.
     * @return array
     */
    public function getUncommitted()
    {
        return array();
    }

    /**
     * Implemented by sub classes.
     * @return null
     */
    public function getId()
    {
        return null;
    }

    /**
     * Return data as JSON string.
     * @return string
     */
    public function __toString()
    {
        return $this->asJSON();
    }

    /**
     * Return data as JSON.
     * @return string
     */
    public function asJSON()
    {
        return json_encode($this->getValues());
    }

    /**
     * When handled by LudoDBRequestHandler no services will by default be cached. This method should
     * be implemented by sub classes when needed.
     * @param string $service
     * @return bool
     */
    public function shouldCache($service)
    {
        return false;
    }

    /**
     * Implemented by sub classes.
     * @return mixed
     */
    abstract public function getValues();

    /**
     * Clear database cache for this instance.
     */
    protected function clearCache()
    {
        if ($this->shouldCache("read") && !empty($this->arguments)) {
            LudoDBCache::clearBy(get_class($this) . "_" . implode("_", $this->arguments));
        }
    }

    /**
     * Return data for this instance.
     * @return mixed
     */
    public function read()
    {
        return $this->getValues();
    }

    /**
     *
     * Returns true if database table has rows where one of the given columns has one of the
     * given values.
     *
     * Example:
     *
     * <code>
     * if($this->hasRowWith(array("email" => "name@dhtmlgoodies.com"));
     * </code>
     *
     * @param array $columnsEqual
     *
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
