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
    private $tableName;

    public function getTableName()
    {
        if (!isset($this->tableName)) {
            $model = $this->getModel();
            if (isset($model)) $this->tableName = $model->configParser()->getTableName();
        }

        return $this->tableName ;
    }

    /**
     * @return LudoDBModel|null
     */
    public function getModel()
    {
        if (isset($this->config['model'])) {
            if (!isset($this->model)) {
                $this->model = $this->getModelInstance();
            }
            $this->model->clearValues();
            return $this->model;
        }
        return null;
    }

    /**
     * @return LudoDBModel
     */
    private function getModelInstance()
    {
        $modelName = $this->config['model'];
        return new $modelName;
    }
}
