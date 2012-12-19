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
    }

    /**
     * @test
     */
    public function shouldBeAbleToOutputJSONOfSimpleObjects(){
        // given
        $car = new Car(1);

        // when
        $json = $car->getJSON();
        $asArray = json_decode($json, true);

        // then
        $this->assertEquals('1', $asArray['id']);
        $this->assertEquals('Opel', $asArray['brand']);
    }
}
