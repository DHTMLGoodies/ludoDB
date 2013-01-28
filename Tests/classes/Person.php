<?php
class Person extends LudoDBModel
{
    protected $JSONConfig = true;

    public function setFirstname($firstname){
        $this->setValue('firstname', $firstname);
    }
}
