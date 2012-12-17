<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 19.11.12
 * Time: 23:29
 * To change this template use File | Settings | File Templates.
 */
class City extends LudoDbTable
{
    protected $tableName = 'City';
    protected $idField = 'zip';
    protected $config = array(
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
}
