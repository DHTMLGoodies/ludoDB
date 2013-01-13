<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 12.01.13
 * Time: 20:29
 */
require_once(__DIR__ . "/../autoload.php");

class PerformanceTest extends TestBase
{
    private $startTime;

    public function setUp(){
        parent::setUp();
        $this->startTime = $this->getTime();
        $person = new Person();
        $person->deleteTableData();
    }

    private function getTime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    private function getElapsed($test){
        $ret = $this->getTime() - $this->startTime;
        $this->logTime($test, $ret);
        return $ret;
    }

    private function logTime($test, $elapsed){

        $time = new TestTimer();
        if(!$time->exists())$time->createTable();
        $time->setTestName("TEST: ". $test);
        $time->setTestTime($elapsed);
        $time->setTestDate(date("Y-m-d H:i:s"));
        $time->commit();
    }

    /**
     * @test
     */
    public function shouldCreateRecordsInAcceptableTime(){
        // given
        for($i=0;$i<100;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setAddress('Somewhere');
            $person->commit();
        }

        $time = $this->getElapsed(__FUNCTION__);

        // then
        $this->assertLessThan(.5, $time);
    }
}
