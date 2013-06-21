<?php
/**
 * LudoDB progress bar implementation for LudoJS
 * User: Alf Magne
 * Date: 17.06.13
 * Time: 14:23
 */
class LudoDBProgress extends LudoDBModel implements LudoDBService
{

    protected $config = array(
        'sql' => 'select * from LudoDBProgress where id = ?',
        'table' => 'LudoDBProgress',
        'columns' => array(
            'id' => array(
                'db' => 'varchar(128) unique not null primary key',
                'access' => 'rw'
            ),
            'created' => 'timestamp',
            'steps' => array(
                'db' => 'int',
                'access' => 'rw'
            ),
            'text' => array(
                'db' => 'text',
                'access' => 'rw'
            ),
            'current' => array(
                'db' => 'int',
                'access' => 'rw'
            )
        )
    );

    public function save($id){
        if(!$this->exists()){
            $this->createTable();
        }

        LudoDB::getInstance()->query("delete from ". $this->configParser()->getTableName()." where current=steps");
        $this->setValue('id', $id);
        $this->setValue('steps',1000);
        $this->setValue('text','');
        $this->setValue('current', 0);
        $this->commit();
    }

    public function read(){
        $ret = parent::read();
        $ret['percent'] = round($ret['current'] / $ret['steps'] * 100);
        return $ret;

    }

    public function getValidServices(){
        return array('read','save');
    }

    public function validateArguments($service, $arguments){
        if($service === 'read') return !empty($arguments) && count($arguments) === 1;
        return count($arguments) === 1;
    }

    public function validateServiceData($service, $data){
        return empty($data);
    }

    public function increment($steps = 1, $text = null){
        if(!$this->getValue('id'))return;
        $this->setValue('current', $this->getValue('current') + $steps);
        if(isset($text))$this->setText($text);
        $this->commit();

    }

    public function finish(){
        if(!$this->getValue('id'))return;
        $this->setValue('current', $this->getValue('steps'));
        $this->commit();
    }

    private function setText($text){
        $this->setValue('text', $text);
    }

    public function setSteps($steps, $text = null){
        if(!$this->getValue('id'))return;
        $this->setValue('steps', $steps);
        if(isset($text))$this->setText($text);
        $this->commit();
    }

    private static $instance;

    /**
     * Return LudoDBProgress Instance
     * @return LudoDBProgress
     */
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new LudoDBProgress();
        }
        return self::$instance;
    }
}
