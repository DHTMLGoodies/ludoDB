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
        $json = $car;
        $asArray = json_decode($json, true);

        // then
        $this->log($json);
        $this->assertEquals('1', $asArray['id']);
        $this->assertEquals('Opel', $asArray['brand']);
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

        $car->JSONPopulate($array);

        $newCar = new Car(1);

        // then
        $this->assertEquals('BMW', $newCar->getBrand());
    }

    /**
     * @test
     */
    public function shouldGetAliasNameOfColumns(){
        // given
        $section = new Section();

    }
}
