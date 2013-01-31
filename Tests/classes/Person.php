<?php
class Person extends LudoDBModel
{
    protected $JSONConfig = true;

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
}
