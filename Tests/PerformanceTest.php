<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 12.01.13

 */
require_once(__DIR__ . "/../autoload.php");

class PerformanceTest extends TestBase
{
    private $startTime;

    public function setUp(){
        parent::setUp();
        LudoDB::setConnectionType('PDO');
        $this->startTimer();
        $person = new Person();
        $person->deleteTableData();
    }

    private function startTimer(){
        $this->startTime = $this->getTime();
    }

    private function getTime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    private function getElapsed($test){
        $ret = $this->getTime() - $this->startTime;
        $ret = number_format($ret, 3);
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
    public function shouldCreate500RecordsInAcceptableTime(){
        // given
        for($i=0;$i<500;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setAddress('Somewhere');
            $person->commit();
        }

        // when
        $time = $this->getElapsed(__FUNCTION__);

        // then
        $this->assertLessThan(2.5, $time);
    }

    /**
     * @test
     */
    public function shouldGetValuesInCollectionInAcceptableTime(){

        // given
        for($i=0;$i<500;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setAddress('Somewhere');
            $person->setZip(4330);
            $person->commit();
        }
        $this->startTimer();
        // when
        $people = new PeoplePlain(4330);
        $values = $people->getValues();
        $time = $this->getElapsed(__FUNCTION__);
        // then
        $this->assertEquals(500, count($values));
        $this->assertLessThan(.1, $time);
    }
    /**
     * @test
     */
    public function shouldGetValuesInCollectionInAcceptableTime_MYSQLI(){
        // given
        LudoDB::setConnectionType('MYSQLI');
        for($i=0;$i<500;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setAddress('Somewhere');
            $person->setZip(4330);
            $person->commit();
        }
        $this->startTimer();
        // when
        $people = new PeoplePlain(4330);
        $values = $people->getValues();
        $time = $this->getElapsed(__FUNCTION__);
        // then
        $this->assertEquals(500, count($values));
        $this->assertLessThan(.1, $time);
    }
    /**
     * @test
     */
    public function shouldGetValuesInModelCollectionInAcceptableTime_PDO(){
        // given
        for($i=0;$i<500;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setAddress('Somewhere');
            $person->setZip(4330);
            $person->commit();
        }
        $this->startTimer();
        // when
        $people = new People(4330);
        $values = $people->getValues();
        $time = $this->getElapsed(__FUNCTION__);
        // then
        $this->assertEquals(500, count($values));
        $this->assertLessThan(.5, $time);
    }

    /**
     * @test
     */
    public function shouldCreateRecordsInAcceptableTime_PDO(){
        $this->startTimer();
        for($i=0;$i<500;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setAddress('Somewhere');
            $person->setZip(4330);
            $person->commit();
        }

        $time = $this->getElapsed(__FUNCTION__);
        // then
        $this->assertLessThan(1.5, $time);
    }
    /**
     * @test
     */
    public function shouldCreateRecordsInAcceptableTime_MYSQLI(){
        LudoDB::setConnectionType('MYSQLI');

        $this->startTimer();
        for($i=0;$i<500;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setAddress('Somewhere');
            $person->setZip(4330);
            $person->commit();
        }

        $time = $this->getElapsed(__FUNCTION__);
        // then
        $this->assertLessThan(1.5, $time);
    }
}
