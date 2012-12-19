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
            array('brand' => 'Opel'),
            array('brand' => 'Volkswagen'),
            array('brand' => 'Chevrolet'),
            array('brand' => 'Audi', 'model' => 'A4'),
            array('brand' => 'Audi', 'model' => 'A3'),
            array('brand' => 'Audi', 'model' => 'A6'),
        )
    );

    public function getBrand(){
        return $this->getValue('brand');
    }

    public function getModel(){
        return $this->getValue('model');
    }
}
