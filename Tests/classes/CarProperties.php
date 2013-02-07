<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 23.12.12

 */
class CarProperties extends LudoDBCollection
{
    protected $config = array(
        'sql' => "select property,propertyValue from carProperty where car_id=?",
        'columns' => array('property','propertyValue'),
        'constructBy' => array('car_id')
    );

    public function key(){
        return $this->currentRow['property'];
    }

    public function current(){
        return $this->currentRow['propertyValue'];
    }
}
