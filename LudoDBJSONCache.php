<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13
 * Time: 12:36
 * To change this template use File | Settings | File Templates.
 */
class LudoDBJSONCache extends LudoDBModel
{
    protected $JSONConfig = false;
    protected $config = array(
        'table' => 'JSON_cache',
        'sql' => "select class_name, JSON_key, JSON_value from JSON_cache where JSON_key='?'",
        'idField' => 'JSON_key',
        'columns' => array(
            'id' => array(
                'db' => 'int auto_increment not null primary key'
            ),
            'JSON_key' => array(
                'db' => 'varchar(512) not null unique',
                'access' => 'rw'
            ),
            'class_name' => array(
                'db' => 'varchar(512)',
                'access' => 'rw'
            ),
            'JSON_value' => array(
                'db' => 'mediumtext',
                'access' => 'rw'
            )
        ),
        'indexes' => array('JSON_key','class_name')
    );

    private $JSON = null;

    public function __construct(LudoDBObject $model = null){
        if(isset($model)){
            $key = $model->getJSONKey();
            parent::__construct($key);
            $this->setClassName(get_class($model));
            $this->setKey($key);
            $this->JSON = $this->getValue('JSON_value');
        }else{
            parent::__construct();
        }
    }

    public function hasValue(){
        return isset($this->JSON) && strlen($this->JSON)>0;
    }

    private function setClassName($name){
        $this->setValue('class_name', $name);
    }

    public function __toString(){
        return $this->getJSON();
    }

    public function getJSON(){
        $ret = $this->getValue('JSON_value');
        return isset($ret) ? $ret : '';
    }

    public function setKey($key){
        $this->setValue('JSON_key', $key);
        return $this;
    }

    public function setJSON($json){
        $this->setValue('JSON_value', $json);
        return $this;
    }

    public static function clearCacheBy($key){
        if(isset($key) && strlen($key)){
            LudoDb::getInstance()->query("delete from JSON_cache where JSON_key='". $key."'");
        }
    }

    public static function clearCacheByClass($className){
        LudoDb::getInstance()->query("delete from JSON_cache where class_name='". $className."'");
    }
}
