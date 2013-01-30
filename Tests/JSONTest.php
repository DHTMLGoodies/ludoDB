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

        $json = new LudoDBCache();
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
        $json = json_decode($car->asJSON(), true);

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
        $this->triggerJSONFor('Capitals', array(5000,6000));
        #$capitals->asJSON();

        // when
        $json = new LudoDBCache($capitals);

        // then
        $val = $this->getDb()->getValue("select count(ID) from ludo_db_cache where class_name='Capitals'");
        $this->assertEquals(1, $val);
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
        $capital = new Capital(101);
        $capital->setName('Stavanger');
        $capital->commit();

        $capitals = new Capitals(5000,6000);
        $json = new LudoDBCache($capitals);

        $this->log($json->getCache());

        // then
        $this->assertFalse($json->hasValue());
    }


    /**
     * @test
     */
    public function shouldDeleteJSONCacheWhenRecordIsDeleted(){
        // given
        $capital = new Capital(1);
        $this->triggerJSONFor('Capital', 1);

        $this->assertEquals(1, $capital->getId());
        $this->assertEquals('Oslo', $capital->getName(), 'Initial test');
        $json = new LudoDBCache($capital);
        $this->assertTrue($json->hasValue(), 'Initial test');

        // when
        $capital->delete();
        $capital = new Capital(1);
        $json = new LudoDBCache($capital);

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
        $this->triggerJSONFor('Capital', 2);
        $capital->asJSON(); // Trigger JSON caching
        $capital->setName('Stavanger');
        $capital->commit();

        // when
        $json = new LudoDBCache($capital);

        // then
        $this->assertFalse($json->hasValue());
    }

    /**
     * @test
     */
    public function shouldGetNewCacheAfterRecordHasBeenUpdated(){
        // given
        $capital = new Capital(2);
        $this->triggerJSONFor('Capital', 2);
        $capital->setName('Stavanger');
        $capital->commit();

        // when
        $this->triggerJSONFor('Capital', 2);
        $json = new LudoDBCache($capital);
        $values = $json->getCache();

        // then
        $this->assertEquals('Stavanger', $values['name']);
    }

    /**
     * @test
     */
    public function shouldDetermineWhenCachingIsEnabled(){
        // given
        $capital = new Capital();

        // then
        $this->assertTrue($capital->JSONCacheEnabled());
    }

    /**
     * @test
     */
    public function shouldReturnCorrectJSONString(){
        // given
        $this->createCapitalCollection();
        $capital = new Capital(100);

        // when
        $json = $capital->asJSON();
        $decoded = json_decode($json, true);

        // then
        $this->assertEquals('4000', $decoded['zip']);
    }

    private function createCapitalCollection(){
        $c = new Capital();
        $c->drop()->yesImSure();
        $c->createTable();
        // given
        $cities = array(
            array('id' => 100, 'zip' => 4000, 'name' => 'Stavanger'),
            array('id' => 101,'zip' => 5500, 'name' => 'Haugesund'),
            array('id' => 102,'zip' => 5501, 'name' => 'Haugesund'),
            array('id' => 103,'zip' => 5502, 'name' => 'Haugesund'),
            array('id' => 104,'zip' => 5503, 'name' => 'Haugesund'),
        );

        foreach($cities as $c){
            $city = new Capital();
            $city->setValues($c);
            $city->commit();
        }
    }

    private function triggerJSONFor($className, $arguments){
        $request = new LudoRequestHandler();
        $request->handle(
            array(
                'model' => $className,
                'data' => $arguments,
                'action' => 'read'
            )
        );
    }


}
