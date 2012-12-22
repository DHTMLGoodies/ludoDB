<?php
class Car extends LudoDbTable
{
    protected $config = array(
        'table' => 'Car',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'brand' => 'varchar(64)',
            'model' => 'varchar(64)',
            'properties' => array(
                'class' => 'CarProperties',
                'lookupField' => 'car_id'
            )
        ),
        'data' => array(
            array('id'=>'1', 'brand' => 'Opel'),
            array('id'=>'2', 'brand' => 'Volkswagen'),
            array('id'=>'3', 'brand' => 'Chevrolet'),
            array('id'=>'4', 'brand' => 'Audi', 'model' => 'A3'),
            array('id'=>'5', 'brand' => 'Audi', 'model' => 'A4'),
            array('id'=>'6', 'brand' => 'Audi', 'model' => 'A5'),
            array('id'=>'7', 'brand' => 'Audi', 'model' => 'A6'),
        )
    );

    public function getProperties(){
        return $this->getValue('properties');
    }

    public function setModel($model){
        $this->setValue('model', $model);
    }

    public function setBrand($brand){
        $this->setValue('brand', $brand);
    }

    public function getBrand(){
        return $this->getValue('brand');
    }

    public function getModel(){
        return $this->getValue('model');
    }
}
