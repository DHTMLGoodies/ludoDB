<?php

class Country extends LudoDbTable{
    protected $tableName = 'Country';
    protected $config = array(
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'name' => 'varchar(64)'
        ),
        'collections' => array(
            'cities' => array(
                'table' => 'city',
                'pk' => 'countryId',
                'fk' => 'id',
                'orderBy' => 'city',
                'columns' => array('city','zip')
            )
        )
    );

    public function setName($name){
        $this->setValue('name', $name);
    }

    public function getName(){
        return $this->getValue('name');
    }
}