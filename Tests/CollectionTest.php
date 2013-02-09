<?php
/**
 * Class for collections
 * User: Alf Magne Kalleland
 * Date: 19.12.12

 */
require_once(__DIR__."/../autoload.php");

class CollectionTest extends TestBase
{
    public function setUp(){
        parent::setUp();
        $car = new Car();
        $car->drop()->yesImSure();
        $car->createTable();

        $pr = new CarProperty();
        $pr->drop()->yesImSure();
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
        $cars = $c->getValues();
        // then
        $this->assertEquals('A3', $cars[0]['model']);
        $this->assertEquals('A4', $cars[1]['model']);
        $this->assertEquals('A5', $cars[2]['model']);
        $this->assertEquals('A6', $cars[3]['model']);

    }

    /**
     * @test
     */
    public function shouldBeAbleToDeleteRecordsInCollection(){
        // given
        $c = new CarCollection('Audi');
        // when
        $c->deleteRecords();
        $c = new CarCollection('Audi');
        $cars = $c->getValues();
        // then
        $this->assertEquals(0, count($cars));

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

        $this->assertTrue(is_numeric($id));
        $this->addCarProperty($id, 'weight','1450kg');
        $this->addCarProperty($id, 'hp', '145');

        // when
        $car = new Car($id);

        $this->assertEquals($id, $car->getId());
        $properties = $car->getProperties();
        $expected = array(
            'weight' => '1450kg',
            'hp' => '145'
        );
        // then
        $this->assertEquals($expected, $properties);
    }

    /**
     * @test
     */
    public function shouldGetPublicNamesDefinedInModelInCollection(){
        // given
        $this->createPersons();

        // when
        $people = new People(4330);
        $values = $people->getValues();
        $john = $values[0];
        // then
        $this->log($john);
        $this->assertEquals(4, count($values));
        $this->assertArrayNotHasKey('nick_name', $john);
        $this->assertArrayHasKey('nick', $john);
        $this->assertEquals('Mr J', $john['nick']);
    }

    /**
     * @test
     */
    public function shouldGetValuesFromExternalTablesWhenUsingModel(){
        // given
        $city = new City();
        $city->deleteTableData();
        $person = new Person();
        $person->deleteTableData();

        $city->setZip(4330);
        $city->setCity('Aalgaard');
        $city->commit();
        $this->assertEquals(1, $this->getDb()->countRows("select zip from city where zip=?", array(4330)));

        $this->createPersons();

        // when
        $people = new People(4330);
        $values = $people->getValues();
        $first = $values[0];
        $this->log($values);
        $this->log($first);

        // then

        $this->assertEquals('John', $first['firstname']);
        $this->assertEquals('4330', $first['zip']);
        $this->assertEquals('Aalgaard', $first['city']);
    }

    /**
     * @test
     */
    public function shouldBeAbleToMerge(){
        // given
        $car = new Car();
        $car->deleteTableData()->yesImSure();
        $car->setBrand("Volkswagen");
        $car->setModel("Polo 1.6 TDI");
        $car->commit();
        $this->addCarProperty($car->getId(), "Hk", "75");
        $this->addCarProperty($car->getId(), "Mileage", "42000");
        $this->addCarProperty($car->getId(), "Year", "2010");

        $cars = new CarsWithProperties();

        // when
        $values = $cars->getValues();
        $firstCar = $values[0];

        // then
        $this->assertNotNull($firstCar['properties']);


    }

    private function createPersons(){
        $persons =  array(
            array('firstname' => 'John', 'lastname' => 'Johnson', 'zip' => '4330','nick' => 'Mr J'),
            array('firstname' => 'Jane', 'lastname' => 'Hansen', 'zip' => '4330', 'nick' => 'Ms J'),
            array('firstname' => 'Mike', 'lastname' => 'Peterson', 'zip' => '4330'),
            array('firstname' => 'Hannah', 'lastname' => 'Jensin', 'zip' => '4330'),
        );
        foreach($persons as $person){
            $p = new Person();
            $p->setValues($person);
            $p->commit();
        }
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
        $this->createCity();
        $person->setZip(4330);
        $person->commit();
        $id = $person->getId();

        foreach($phoneNumbers as $number){
            $this->addPhone($id, $number);
        }
        return new Person($id);
    }

    private function createCity(){
        $city = new City(4330);
        if(!$city->getId()){
            $city->setZip(4330);
            $city->setCity("Aalgaard");
            $city->commit();
        }
    }

    private function addPhone($personId, $number){
        $phone = new Phone();
        $phone->setUserId($personId);
        $phone->setPhone($number);
        $phone->commit();
    }
}
