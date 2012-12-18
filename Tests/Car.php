<?php
class Car extends LudoDbTable
{
    protected $tableName = 'Car';
    protected $config = array(
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'brand' => 'varchar(64)'
        ),
        'data' => array(
            array('brand' => 'Opel'),
            array('brand' => 'Volkswagen'),
            array('brand' => 'Chevrolet')

        )
    );

    public function getBrand(){
        return $this->getValue('brand');
    }
}
