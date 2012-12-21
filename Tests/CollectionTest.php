<?php
/**
 * Class for collections
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:24
 */
require_once(__DIR__."/../autoload.php");

class CollectionTest extends TestBase
{
    public function setUp(){
        parent::setUp();
        $car = new Car();
        $car->drop();
        $car->createTable();
    }
    /**
     * @test
     */
    public function shouldBeAbleToGetCollection(){
        // given
        $c = new CarCollection('Audi');
        $result = array();
        // when
        foreach($c as $key=>$car){
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
    public function shouldBeAbleToGetArrayUsingGetValue(){
        // given
        $c = new CarCollection('Audi');
        // when
        $cars = $c->getValue();
        // then
        $this->assertEquals('A3', $cars[0]['model']);
        $this->assertEquals('A4', $cars[1]['model']);
        $this->assertEquals('A5', $cars[2]['model']);
        $this->assertEquals('A6', $cars[3]['model']);

    }

    /**
     * @test
     */
    public function shouldReturnValueWhenCollectionIsDefinedAsColumnForTable(){
        

    }
}
