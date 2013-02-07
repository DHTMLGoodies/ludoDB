<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 12.01.13

 */
class TestTimer extends LudoDBModel
{
    protected $JSONConfig = true;

    public function setTestName($name){
        $this->setValue('test_name', $name);
    }

    public function setTestTime($time){
        $this->setValue('test_time', $time);
    }

    public function setTestDate($date){
        $this->setValue('test_date', $date);
    }
}
