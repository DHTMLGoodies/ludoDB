<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 19.11.12
 * Time: 23:28
 * To change this template use File | Settings | File Templates.
 */
class Person extends LudoDbTable
{
    protected $tableName = 'Person';
    protected $idField = 'id';
    protected $config = array(
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'firstname' => 'varchar(32)',
            'lastname' => 'varchar(32)',
            'address' => 'varchar(64)',
            'zip' => 'varchar(5)'
        ),
        'join' => array(
            array('table' => 'city', 'pk' => 'zip', 'fk' => 'zip', 'columns' => array('city'))
        )

    );

    public function setFirstname($value){
        $this->setValue('firstname', $value);
    }

    public function setZip($value){
        $this->setValue('zip', $value);
    }

    public function getCity(){
        return $this->getColumnValue('city');
    }
}
