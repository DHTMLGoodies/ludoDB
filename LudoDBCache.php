<?php
/**
 * Caching of serialized LudoDBObjects
 * User: Alf Magne
 * Date: 28.01.13
 */
class LudoDBCache extends LudoDBModel
{
    protected $JSONConfig = false;
    protected $config = array(
        'table' => 'ludo_db_cache',
        'sql' => "select class_name, cache_key, cache_value from ludo_db_cache where cache_key=?",
        'idField' => 'JSON_key',
        'columns' => array(
            'id' => array(
                'db' => 'int auto_increment not null primary key'
            ),
            'cache_key' => array(
                'db' => 'varchar(512)',
                'access' => 'rw'
            ),
            'class_name' => array(
                'db' => 'varchar(512)',
                'access' => 'rw'
            ),
            'cache_value' => array(
                'db' => 'mediumtext',
                'access' => 'rw'
            )
        ),
        'indexes' => array('cache_key','class_name')
    );

    private $JSON = null;

    public function __construct(LudoDBService $resource = null, array $arguments = null){
        if(isset($resource)){
            $resourceName = get_class($resource);
            $cacheKey = $this->getCacheKey($resourceName, $arguments);
            parent::__construct($cacheKey);
            if(isset($cacheKey)){
                $this->setKey($cacheKey);
                $this->setClassName($resourceName);
                $this->JSON = $this->getValue('cache_value');
            }
        }else{
            parent::__construct();
        }
    }

    private function getCacheKey($resourceName, $arguments){
        return implode("_", array_merge(array($resourceName), $arguments));
    }

    public function hasData(){
        return isset($this->JSON) && strlen($this->JSON)>0;
    }

    private function setClassName($name){
        $this->setValue('class_name', $name);
    }

    public function getCache(){
        $ret = $this->getValue('cache_value');
        return isset($ret) && strlen($ret)> 0 ? unserialize($ret) : '';
    }

    public function setKey($key){
        $this->setValue('cache_key', $key);
        return $this;
    }

    public function setCache(array $json){
        $this->setValue('cache_value', serialize($json));
        return $this;
    }

    public static function clearBy($key){
        if(isset($key) && strlen($key)){
            LudoDb::getInstance()->query("delete from ludo_db_cache where cache_key=?", array($key));
        }
    }

    public static function clearByClass($className){
        LudoDb::getInstance()->query("delete from ludo_db_cache where class_name=?", array($className));
    }
}
