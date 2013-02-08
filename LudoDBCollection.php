<?php
/**
 * LudoDB collection class
 * User: Alf Magne Kalleland
 * Date: 19.12.12

 */
abstract class LudoDBCollection extends LudoDBIterator
{
    /**
     * Lookup value to use when instantiating collection. This value
     * is used in join with config['constructBy']
     */
    protected $arguments;

    public function deleteRecords(){
        if(isset($this->arguments)){
            $constructBy = $this->parser->getConstructorParams();
            $this->db->query("delete from ". $this->parser->getTableName()." where ". $constructBy[0]."=?", array($this->arguments[0]));
            $this->clearCache();
        }
    }

    protected function getConfigParserInstance(){
        return new LudoDBCollectionConfigParser($this, $this->config);
    }

    public function getValues(){
        $model = $this->parser->getModel();
        if(isset($model)){
            return $this->getValuesUsingModel($model);
        }else{
            return parent::getValues();
        }
    }

    private function getValuesUsingModel(LudoDBModel $model){
        $model->disableCommit();
        $ret = array();
        $key = $this->parser->getGroupBy();
        $staticValues = $model->parser->getStaticValues();

        foreach($this as $value){
            if(!isset($columns))$columns = array_keys($value);
            $model->clearValues();
            $model->setValues($value);
            $modelValues = $this->getValuesFromModel($model, $columns);
            $modelValues = array_merge($modelValues, $staticValues);
            if(isset($key) && isset($modelValues[$key])){
                if(!isset($ret[$modelValues[$key]])){
                    $ret[$modelValues[$key]] = array();
                }
                $ret[$modelValues[$key]][] = $modelValues;
            }else{
                $ret[] = $modelValues;
            }
        }
        $model->enableCommit();
        return $ret;
    }

    /**
     * @param LudoDBModel $model
     * @param array $columns
     * @return array
     */
    protected function getValuesFromModel($model, $columns){
        return $model->getSomeValues($columns);
    }

    public function getJSONKey(){
        $ret = get_class($this);
        if(isset($this->arguments) && count($this->arguments)){
            $ret.="_". implode("_", $this->arguments);
        }
        return $ret;
    }

    /**
     * Return values of a column as array
     * @param String $column
     * @return array
     */
    protected function getColumnValues($column){
        $values = parent::getValues();
        $ret = array();
        foreach($values as $value){
            if(isset($value[$column])){
                $ret[] = $value[$column];
            }
        }
        return $ret;
    }
}
