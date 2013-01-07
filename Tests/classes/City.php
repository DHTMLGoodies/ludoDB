<?php

class City extends LudoDbTable
{
    protected $config = array(
        'idField' => 'zip',
        'table' => 'City',
        'columns' => array(
            'zip' => 'varchar(32) primary key',
            'city' => 'varchar(64)',
            'countryId' => 'int'
        ),
        'indexes' => array('countryId')
    );

    public function setZip($zip){
        $this->setValue('zip', $zip);
    }

    public function setCity($city){
        $this->setValue('city', $city);
    }

    public function setCountryId($countryId){
        $this->setValue('countryId', $countryId);
    }

    public function getCity(){
        return $this->getValue('city');
    }
}
