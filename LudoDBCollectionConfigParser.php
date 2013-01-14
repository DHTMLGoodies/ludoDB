<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 14.01.13
 * Time: 14:16
 * To change this template use File | Settings | File Templates.
 */
class LudoDBCollectionConfigParser extends LudoDBConfigParser
{

    private $model = null;

    public function getTableName()
    {
        $model = $this->getModel();
        if(isset($model))return $model->configParser()->getTableName();
        return null;
    }

    /**
     * @return LudoDBTable|null
     */
    private function getModel(){
        if(isset($this->config['model'])){
            if(!isset($this->model)){
                $this->model = new $this->config['model'];
            }
            return $this->model;
        }
        return null;
    }
}
