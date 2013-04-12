<?php
/**
 * Caching of serialized LudoDBObjects
 * User: Alf Magne
 * Date: 28.01.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Cache class used by LudoDBRequestHandler. This is a LudoDBModel storing data as serialized
 * strings in the database. When caching is enabled for a service, LudoDBRequestHandler will
 * attempt to retrieve data from cache before calling the service method. This may save time
 * for services requiring many SQL queries to complete.
 * @package LudoDB
 */
class LudoDBCache extends LudoDBModel
{
    /**
     * JSONConfig set to false
     * @var bool
     */
    protected $JSONConfig = false;
    /**
     * Config of ludo_db_cache
     * @var array
     */
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

    /**
     * Internal JSON cache
     * @var null
     */
    private $JSON = null;

    /**
     * Creates new LudoDBCache instance. An empty LudoDBService class is sent to the constructor along
     * with constructor arguments. The cache class will search for records where "class_name" equals
     * class of given service and arguments compiled to a key matches cache_key in the database table.
     * @param LudoDBService $resource
     * @param array $arguments
     */
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

    /**
     * Return cache key based on class name and arguments.
     * @param $resourceName
     * @param $arguments
     * @return string
     */
    private function getCacheKey($resourceName, $arguments){
        return implode("_", array_merge(array($resourceName), $arguments));
    }

    /**
     * Returns true if cache data exists.
     * @return bool
     */
    public function hasData(){
        return isset($this->JSON) && strlen($this->JSON)>0;
    }

    /**
     * Set class name for new cache record
     * @param $name
     */
    private function setClassName($name){
        $this->setValue('class_name', $name);
    }

    /**
     * Return cache data
     * @return array
     */
    public function getCache(){
        $ret = $this->getValue('cache_value');
        return isset($ret) && strlen($ret)> 0 ? unserialize($ret) : '';
    }

    /**
     * Set cache_key of new cache record
     * @param $key
     * @return LudoDBCache
     */
    public function setKey($key){
        $this->setValue('cache_key', $key);
        return $this;
    }

    /**
     * Store service data to cache
     * @param array $json
     * @return LudoDBCache
     */
    public function setCache(array $json){
        $this->setValue('cache_value', serialize($json));
        return $this;
    }

    /**
     * Clear cache records from db where cache_key equals key
     * @param string $key
     */
    public static function clearBy($key){
        if(isset($key) && strlen($key)){
            LudoDb::getInstance()->query("delete from ludo_db_cache where cache_key=?", array($key));
        }
    }

    /**
     * Clear all cache record for given class name / resource.
     * @param $className
     */
    public static function clearByClass($className){
        LudoDb::getInstance()->query("delete from ludo_db_cache where class_name=?", array($className));
    }
}
