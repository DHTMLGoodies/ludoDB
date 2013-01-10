<?php
class Client extends LudoDbTable
{
    protected $JSON = true;

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
