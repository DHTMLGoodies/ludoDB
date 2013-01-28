<?php
/**
 * JSON output of DB records
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 17:02
 */

require_once(__DIR__ . "/../autoload.php");

class JSONTest extends TestBase
{
    public function setUp(){
        parent::setUp();

        $car = new Car();
        $car->drop();
        $car->createTable();

        $section = new Section();
        $section->drop();
        $section->createTable();
    }

    /**
     * @test
     */
    public function shouldBeAbleToOutputJSONOfSimpleObjects(){
        // given
        $car = new Car(1);

        // when
        $json = $car->getValues();

        // then
        $this->assertEquals('1', $json['id']);
        $this->assertEquals('Opel', $json['brand']);
    }

    /**
     * @test
     */
    public function shouldBeAbleToPopulateByJSON(){
        // given
        $car = new Car(1);
        $this->assertEquals(1, $car->getId());
        $json = $car;
        $array = json_decode($json, true);

        // when
        $array['brand'] = 'BMW';

        $car->setValues($array);
        $car->commit();

        $newCar = new Car(1);

        // then
        $this->assertEquals('BMW', $newCar->getBrand());
    }

    public function shouldBeAbleToStoreJSONInJSONCache(){

    }

    /**
     * @test
     */
    public function shouldGetAliasNameOfColumns(){
        // given
        $section = new Section();

    }
}
