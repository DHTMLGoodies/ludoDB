<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 23.12.12
 * Time: 00:34
 */
class CarProperty extends LudoDbTable
{
    protected $config = array(
        'idField' => 'id',
        'table' => 'CarProperty',
        'constructorParams' => 'id',
        'columns' => array(
            'car_id' => 'int',
            'property' => 'varchar(32)',
            'propertyValue' => 'varchar(255)'
        ),
        'indexes' => array('car_id')
    );

    public function setCarId($id){
        $this->setValue('car_id', $id);
    }
    public function setProperty($pr){
        $this->setValue('property', $pr);
    }
    public function setPropertyValue($val){
        $this->setValue('propertyValue', $val);
    }
}
