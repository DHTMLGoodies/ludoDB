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

        $pr = new CarProperty();
        $pr->drop();
        $pr->createTable();
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
        $person = $this->getPersonWithPhone('John', array('555 888', '555 999'));

        // when
        $numbers = $person->getPhone();

        // then
        $this->assertEquals(2, count($numbers));
        $this->assertEquals('555 888', $numbers[0]);
        $this->assertEquals('555 999', $numbers[1]);
    }

    /**
     * @test
     */
    public function shouldBeAbleToGetCollectionAsKeyValue(){
        // given
        $car = new Car();
        $car->setModel('Mercedez');
        $car->commit();

        $id = $car->getId();

        $this->addCarProperty($id, 'weight','1450kg');
        $this->addCarProperty($id, 'hp', '145');

        // when
        $car = new Car($id);
        $properties = $car->getProperties();
        $expected = array(
            'weight' => '1450kg',
            'hp' => '145'
        );

        // then
        $this->assertEquals($expected, $properties);
    }


    private function addCarProperty($carId, $key, $value){
        $pr = new CarProperty();
        if(!$pr->exists())$pr->createTable();
        $pr->setCarId($carId);
        $pr->setProperty($key);
        $pr->setPropertyValue($value);
        $pr->commit();

    }

    private function getPersonWithPhone($firstname = '',$phoneNumbers = array()){
        $person = new Person();
        $person->setFirstname($firstname);
        $person->commit();
        $id = $person->getId();

        foreach($phoneNumbers as $number){
            $this->addPhone($id, $number);
        }
        return new Person($id);
    }

    private function addPhone($personId, $number){
        $phone = new Phone();
        $phone->setUserId($personId);
        $phone->setPhone($number);
        $phone->commit();
    }
}
