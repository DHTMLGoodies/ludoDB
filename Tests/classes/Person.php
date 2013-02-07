<?php
class Person extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true;
    public static $validServices = array('save','delete','read');

    public static function getValidServices(){
        return array('save','delete','read');
    }

    public function setFirstname($firstname){
        $this->setValue('firstname', $firstname);
    }

    public function getPhone(){
        return $this->getValue('phone');
    }

    public function setLastname($lastname){
        $this->setValue('lastname', $lastname);
    }

    public function setZip($zip){
        $this->setValue('zip', $zip);
    }

    public function getFirstname(){
        return $this->getValue('firstname');
    }

    public function setAddress($address){
        $this->setValue('address', $address);
    }

    public function validateService($service, $arguments){
        return empty($arguments) || count($arguments) === 1 && is_numeric($arguments[0]) ? true: false;
    }

    public function getSex(){
        return $this->getValue('sex');
    }
}
