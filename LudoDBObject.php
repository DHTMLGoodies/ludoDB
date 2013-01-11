<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * Time: 14:31
 */
class LudoDBObject
{
    protected $db;
    protected $config = array();
    protected $constructorValues;
    private static $configParsers = array();
    private static $fileLocation;
    protected $JSONConfig = false;
    private $JSONRead = false;

    public function __construct()
    {
        $this->db = LudoDb::getInstance();
        if (func_num_args() > 0) {
            $this->constructorValues = func_get_args();
        }
        $this->onConstruct();
    }

    protected function onConstruct()
    {

    }

    protected function getConfigFromFile(){
        $location = $this->getPathToJSONConfig();
        if(file_exists($location)){
            $content = file_get_contents($location);
            return JSON_decode($content, true);
        }
        return null;
    }

    protected function getPathToJSONConfig()
    {
        return $this->getFileLocation() . "/JSONConfig/" . get_class($this) . ".json";
    }

    protected function getFileLocation()
    {
        if (!isset(self::$fileLocation)) {
            $obj = new ReflectionClass($this);
            self::$fileLocation = dirname($obj->getFilename());
        }
        return self::$fileLocation;
    }

    public function getConstructorValues()
    {
        return $this->constructorValues;
    }

    public function getConfig()
    {
        if($this->JSONConfig && !$this->JSONRead){
            $this->JSONRead = true;
            $this->config = $this->getConfigFromFile();
        }
        return $this->config;
    }

    public function commit()
    {

    }

    /**
     * @return LudoDBConfigParser
     */
    public function configParser()
    {
        $key = get_class($this);
        if (!isset(self::$configParsers[$key])) {
            self::$configParsers[$key] = new LudoDBConfigParser($this);
        }
        return self::$configParsers[$key];
    }

    public static function clearParsers(){
        self::$configParsers = array();
    }
}
