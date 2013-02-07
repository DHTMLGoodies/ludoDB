<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 23.12.12

 */
class CarProperty extends LudoDBModel
{
    protected $JSONConfig = true;

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
