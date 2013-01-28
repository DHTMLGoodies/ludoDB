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
    public function setUp()
    {
        parent::setUp();

        $car = new Car();
        $car->drop()->yesImSure();
        $car->createTable();

        $section = new Section();
        $section->drop()->yesImSure();
        $section->createTable();

        $json = new LudoDBJSONCache();
        $json->drop()->yesImSure();
        $json->createTable();

        $c = new Capital();
        $c->drop()->yesImSure();
        $c->createTable();
    }

    /**
     * @test
     */
    public function shouldBeAbleToOutputJSONOfSimpleObjects()
    {
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
    public function shouldBeAbleToPopulateByJSON()
    {
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

    /**
     * @test
     */
    public function shouldBeAbleToStoreJSONInJSONCache()
    {
        // given
        $this->createCapitalCollection();
        $capitals = new Capitals(5000,6000);
        $this->assertEquals(4, count($capitals->getValues()));
        $json = $capitals->asJSON();

        // when
        $json = new LudoDBJSONCache($capitals);

        // then
        $this->assertTrue($json->hasValue());

    }

    /**
     * @test
     */
    public function shouldReturnProperJSONKey(){
        // given
        $capital = new Capital(1);

        // then
        $this->assertEquals('Capital_1', $capital->getJSONKey());
    }

    /**
     * @test
     */
    public function shouldDeleteCacheOfParentsWhenUpdated(){
        // given
        $this->createCapitalCollection();
        $capitals = new Capitals(5000,6000);
        $capitals->asJSON(); // Trigger caching

        // when
        $capital = new Capital(1);
        $capital->setName('Stavanger');
        $capital->commit();

        $capitals = new Capitals(5000,6000);
        $json = new LudoDBJSONCache($capitals);

        $this->log($json->getJSON());

        // then
        $this->assertFalse($json->hasValue());
    }


    /**
     * @test
     */
    public function shouldDeleteJSONCacheWhenRecordIsDeleted(){
        // given
        $capital = new Capital(1);
        $capital->asJSON(); // Trigger JSON caching
        $this->assertEquals(1, $capital->getId());
        $this->assertEquals('Oslo', $capital->getName(), 'Initial test');
        $json = new LudoDBJSONCache($capital);
        $this->assertTrue($json->hasValue(), 'Initial test');

        // when
        $capital->delete();
        $capital = new Capital(1);
        $json = new LudoDBJSONCache($capital);

        // then
        $this->assertNull($capital->getId());
        $this->assertFalse($json->hasValue());
    }

    /**
     * @test
     */
    public function shouldDeleteCacheWhenRecordIsUpdated(){
        // given
        $capital = new Capital(2);
        $capital->asJSON(); // Trigger JSON caching
        $capital->setName('Stavanger');
        $capital->commit();

        // when
        $json = new LudoDBJSONCache($capital);

        // then
        $this->assertFalse($json->hasValue());
    }

    /**
     * @test
     */
    public function shouldGetNewCacheAfterRecordHasBeenUpdated(){
        // given
        $capital = new Capital(2);
        $capital->asJSON(); // Trigger JSON caching
        $capital->setName('Stavanger');
        $capital->commit();

        // when
        $capital->asJSON(); // Trigger JSON caching
        $json = new LudoDBJSONCache($capital);
        $jsonAsArray = JSON_decode($json->getJSON(), true);

        // then
        $this->assertEquals('Stavanger', $jsonAsArray['name']);

    }

    private function createCapitalCollection(){
        $city = new Capital();
        $city->deleteTableData()->yesImSure();
        // given
        $cities = array(
            array('zip' => 4000, 'name' => 'Stavanger'),
            array('zip' => 5500, 'name' => 'Haugesund'),
            array('zip' => 5501, 'name' => 'Haugesund'),
            array('zip' => 5502, 'name' => 'Haugesund'),
            array('zip' => 5503, 'name' => 'Haugesund'),
        );

        foreach($cities as $c){
            $city = new Capital();
            $city->setValues($c);
            $city->commit();
        }
    }


}
