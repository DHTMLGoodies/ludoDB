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
        // given
        $person = $this->getPersonWithPhone();

        // when
        $numbers = $person->getPhone();

        // then
        $this->assertEquals(2, count($numbers));
        $this->assertEquals('555 888', $numbers[0]);
        $this->assertEquals('555 999', $numbers[1]);
    }

    private function getPersonWithPhone(){
        $person = new Person();
        $person->setFirstname('John');
        $person->commit();
        $id = $person->getId();

        $phone = new Phone();
        $phone->setUserId($id);
        $phone->setPhone('555 888');
        $phone->commit();

        $phone = new Phone();
        $phone->setUserId($id);
        $phone->setPhone('555 999');
        $phone->commit();
        return new Person($id);
    }


}
