<?php
class Car extends LudoDBModel implements LudoDBService
{
    protected $config = array(
        'idField' => 'id',
        'table' => 'Car',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'brand' => array(
                'db' =>'varchar(64)',
                'access' => 'rw'
            ),
            'model' => array(
                'db' =>'varchar(64)',
                'access' => 'rw'
            ),
            'properties' => array(
                'class' => 'CarProperties',
                'fk' => 'id'
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

    public function getValidServices(){
        return array('read','save');
    }

    public function validateServiceData($service, $data){
        if($service === 'read')return empty($data);
        return true;
    }

    public function validateArguments($service, $arguments){
        return true;
    }
}
