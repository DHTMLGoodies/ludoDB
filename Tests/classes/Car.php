<?php
class Car extends LudoDbTable
{
    protected $tableName = 'Car';
    protected $config = array(
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'brand' => 'varchar(64)',
            'model' => 'varchar(64)'
        ),
        'data' => array(
            array('id'=>'1', 'brand' => 'Opel'),
            array('id'=>'2', 'brand' => 'Volkswagen'),
            array('id'=>'3', 'brand' => 'Chevrolet'),
            array('id'=>'4', 'brand' => 'Audi', 'model' => 'A4'),
            array('id'=>'5', 'brand' => 'Audi', 'model' => 'A3'),
            array('id'=>'6', 'brand' => 'Audi', 'model' => 'A6'),
        )
    );

    public function getBrand(){
        return $this->getValue('brand');
    }

    public function getModel(){
        return $this->getValue('model');
    }
}
