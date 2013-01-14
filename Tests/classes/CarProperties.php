<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 23.12.12
 * Time: 00:34
 */
class CarProperties extends LudoDBCollection
{
    protected $config = array(
        'model' => 'CarProperty',
        'columns' => array('property','propertyValue'),
        'constructorParams' => array('car_id')
    );

    public function key(){
        return $this->currentRow['property'];
    }

    public function current(){
        return $this->currentRow['propertyValue'];
    }
}
