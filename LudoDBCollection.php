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
            $constructBy = $this->parser->getConstructorParams();
            $this->db->query("delete from ". $this->parser->getTableName()." where ". $constructBy[0]."='". $this->constructorValues[0]."'");
        }
    }

    protected function getConfigParserInstance(){
        return new LudoDBCollectionConfigParser($this, $this->config);
    }

    public function getValues(){
        $model = $this->parser->getModel();
        if(isset($model)){
            $model->disableCommit();
            $ret = array();
            foreach($this as $value){
                if(!isset($columns))$columns = array_keys($value);
                $model->clearValues();
                $model->setValues($value);
                $ret[] = $this->getValuesFromModel($model, $columns);
            }
            $model->enableCommit();
            return $ret;
        }else{
            return parent::getValues();
        }
    }

    /**
     * @param LudoDBTable $model
     * @param array $columns
     * @return array
     */
    protected function getValuesFromModel($model, $columns){
        return $model->getSomeValues($columns);
    }
}
