<?php
/**
 * Cacne tests
 * User: Alf Magne Kalleland
 * Date: 19.12.12

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

        $c = new LudoDBCache();
       $c->drop()->yesImSure();
       $c->createTable();

        $c = new Capital();
        $c->drop()->yesImSure();
        $c->createTable();
    }

    /**
     * @test
     */
    public function shouldBeAbleToOutputCacheOfSimpleObjects()
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

        // when
        $cache = new LudoDBCache($capitals, array(5000,6000));

        // then
        $val = $this->getDb()->getValue("select count(ID) from ludo_db_cache where class_name='Capitals'");
        $this->assertEquals(1, $val);
        $this->assertTrue($cache->hasData());
    }

    /**
     * @test
     */
    public function shouldDetermineWhenCachingIsEnabled(){
        // given
        $capital = new Capital();

        // then
        $this->assertTrue($capital->shouldCache("read"));
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

    private function triggerJSONFor($className, $arguments = array()){
        $request = new LudoDBRequestHandler();
        $requestString = $className;
        if(!is_array($arguments))$arguments = array($arguments);
        if(!empty($arguments))$requestString.="/".implode("/", $arguments);
        $requestString.="/read";

        $request->handle(
            $requestString
        );
    }
}
