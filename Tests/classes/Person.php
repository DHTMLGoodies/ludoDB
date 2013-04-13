<?php
class Person extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true;

    public function getValidServices(){
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

    public function getZip(){
        return $this->getValue('zip');
    }

    public function getCity(){
        return $this->getValue('city');
    }

    public function getLastname(){
        return $this->getValue('lastname');
    }

    public function getType(){
        return $this->getValue('type');
    }

    public function getCoffee(){
        return $this->getValue('coffee');
    }

    public function getFirstname(){
        return $this->getValue('firstname');
    }

    public function setAddress($address){
        $this->setValue('address', $address);
    }

    public function validateArguments($service, $arguments){
        return empty($arguments) || count($arguments) === 1 && is_numeric($arguments[0]) ? true: false;
    }

    public function validateServiceData($service, $data){
        return true;
    }

    public function getOnSuccessMessageFor($service){
        switch($service){
            case "read":
                return "Succesfully read";
            default:
                return "D";
        }
    }

    public function getSex(){
        return $this->getValue('sex');
    }
}
