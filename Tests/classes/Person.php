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
}
