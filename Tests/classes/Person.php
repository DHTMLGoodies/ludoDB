<?php
class Person extends LudoDbTable
{
    protected $tableName = 'Person';
    protected $idField = 'id';
    protected $config = array(
        'table' => 'Person',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'firstname' => 'varchar(32)',
            'lastname' => 'varchar(32)',
            'address' => 'varchar(64)',
            'zip' => 'varchar(5)',
            'phone' => array(
                'class' => 'PhoneCollection'
            ),
            'city' => array(
                'class' => 'City',
                'method' => 'getCity'
            )
        ),
        'classes' => array(
            'city' => array(
                'fk' => 'zip'
            )
        )
    );

    public function setFirstname($value){
        $this->setValue('firstname', $value);
    }

    public function setLastName($value){
        $this->setvalue('lastname', $value);
    }

    public function setZip($value){
        $this->setValue('zip', $value);
    }

    public function getFirstname(){
        return $this->getvalue('firstname');
    }

    public function getLastname(){
        return $this->getvalue('lastname');
    }

    public function getZip(){
        return $this->getValue('zip');
    }

    public function getCity(){
        return $this->getValue('city');
    }

    public function getPhone(){
        return $this->getValue('phone');
    }

    public function setAddress($address){
        $this->setValue('address',$address);
    }
}
