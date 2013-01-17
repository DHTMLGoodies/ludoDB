<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:31
 */
abstract class LudoDBCollection extends LudoDBIterator
{
    /**
     * Lookup value to use when instantiating collection. This value
     * is used in join with config['constructBy']
     */
    protected $constructorValues;
    protected $config = array();

    public function deleteRecords(){
        if(isset($this->constructorValues)){
            $constructBy = $this->configParser()->getConstructorParams();
            $this->db->query("delete from ". $this->configParser()->getTableName()." where ". $constructBy[0]."='". $this->constructorValues[0]."'");
        }
    }

    protected function getConfigParserInstance(){
        return new LudoDBCollectionConfigParser($this, $this->config);
    }

    public function getValues(){
        $model = $this->configParser()->getModel();
        if(isset($model)){
            $ret = array();
            foreach($this as $key=>$value){
                if(!isset($columns))$columns = array_keys($value);
                if(isset($value)){
                    $model->clearValues();
                    $model->setValues($value);
                    $ret[$key] = $model->getSomeValues($columns);
                }
            }
            return $ret;
        }else{
            return parent::getValues();
        }
    }
}
