<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * Time: 14:31
 */
abstract class LudoDBObject
{
    protected $db;
    protected $config = array();
    protected $constructorValues;
    protected static $configParsers = array();
    protected $JSONConfig = false;
    private $sql_handler;

    public function __construct()
    {
        $this->db = LudoDb::getInstance();
        if (func_num_args() > 0) {
            $this->constructorValues = $this->getValidConstructorValues(func_get_args());
        }
        $this->onConstruct();
    }

    protected function sqlHandler(){
        if(!isset($this->sql_handler)){
            $this->sql_handler = new LudoSQL($this);
        }
        return $this->sql_handler;
    }


    protected function getValidConstructorValues($values){
        foreach($values as &$value){
            $value = $this->db->escapeString($value);
        }
        return $values;
    }

    protected function onConstruct()
    {

    }


    public function hasConfigInExternalFile(){
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
     * @return LudoDBConfigParser
     */
    public function configParser()
    {
        $key = $this->getConfigParserKey();
        if (!isset(self::$configParsers[$key])) {
            self::$configParsers[$key] = $this->getConfigParserInstance();
        }
        return self::$configParsers[$key];
    }

    protected function getConfigParserInstance(){
        return new LudoDBConfigParser($this, $this->config);
    }
    private $configParserKey;
    protected function getConfigParserKey(){
        if(!isset($this->configParserKey)){
            $this->configParserKey = get_class($this);
        }
        return $this->configParserKey;
    }

    public static function clearParsers(){
        self::$configParsers = array();
    }

    public function getUncommitted(){
        return array();
    }

    public function getId(){

    }

    public function __toString()
    {
        return $this->asJSON();
    }

    protected function asJSON(){
        $data = $this->getValues();
        if(LudoDB::isLoggingEnabled()){
            $data['__log'] = array(
                'time' => LudoDB::getElapsed()
            );
        }
        return json_encode($data);
    }

    abstract public function getValues();
}
