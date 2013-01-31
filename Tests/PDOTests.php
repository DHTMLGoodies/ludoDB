<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 11.01.13
 * Time: 15:40
 * To change this template use File | Settings | File Templates.
 */
require_once(__DIR__ . "/../autoload.php");

class PDOTests extends TestBase
{

    public function setUp()
    {

        parent::setUp();

        LudoDb::usePDO();


        $city = new City();
        $city->drop()->yesImSure();
        $city->createTable();

        $phone = new Phone();
        if (!$phone->exists()) $phone->createTable();

        $car = new Car();
        $car->drop()->yesImSure();
        $car->createTable();

        $pr = new CarProperty();
        $pr->drop()->yesImSure();
        $pr->createTable();
    }

    /**
     * @test
     */
    public function shouldBeAbleToGetCollection()
    {
        // given
        $c = new CarCollection('Audi');
        $result = array();
        // when
        foreach ($c as $key => $car) {
            $result[$key] = $car;
        }

        // then
        $this->assertEquals('A3', $result[0]['model']);
        $this->assertEquals('A4', $result[1]['model']);
        $this->assertEquals('A5', $result[2]['model']);
        $this->assertEquals('A6', $result[3]['model']);
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateAndGetObjects()
    {
        // given
        $person = new Person();
        $person->setFirstname('Alf');
        $person->commit();

        // when
        $p = new Person($person->getId());

        // then
        $this->assertEquals('Alf', $p->getFirstname());
    }

}
