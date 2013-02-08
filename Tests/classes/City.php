<?php

class City extends LudoDBModel
{
    protected $config = array(
        'sql' => 'select * from city where zip = ?',
        'idField' => 'zip',
        'table' => 'city',
        'columns' => array(
            'zip' => array(
                'db' => 'varchar(32) primary key',
                'access' => 'rw'
            ),
            'city' => array(
                'db' => 'varchar(64)',
                'access' => 'rw'
            ),
            'countryId' => array(
                'db' => 'int',
                'access' => 'rw'
            ),
            "state" => array(
                "db" => "varchar(32)",
                "access" => "rw"
            )
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
