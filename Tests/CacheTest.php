<?php
/**
 * Cacne tests
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 17:02
 */

require_once(__DIR__ . "/../autoload.php");

class CacheTest extends TestBase
{
    public function setUp()
    {
        parent::setUp();

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
        $this->assertTrue($capital->cacheEnabled());
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
        echo $requestString."\n";
        $request->handle(
            array(
                'request' => $requestString
            )
        );
    }
}
