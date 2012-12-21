<?php
/**
 * PHPUnit tests ludoDB
 * User: Alf Magne Kalleland
 * Date: 04.11.12
 * Time: 02:15
 */
require_once(__DIR__ . "/../autoload.php");

class DBTest extends TestBase
{
    public function setUp(){
        parent::setUp();
    }

    /**
     * @test
     */
    public function shouldBeAbleToRunTests(){
        $this->assertEquals(1,1);
    }

    /**
     * @test
     */
    public function testShouldNotBeAbleToUpdateFieldsNotInConfig(){
        // given
        $table = new TestTable();

        // when
        $table->setFirstName('Alf Magne');

        // then
        $this->assertEquals(1, count($table->getUpdates()));
        $this->assertEquals(array('firstname' => 'Alf Magne'), $table->getUpdates());
    }

    /**
     * @test
     */
    /**
     * @test
     */
    public function shouldBeAbleToCreateTable(){
        $this->dropTable();
        $table = new TestTable();
        $table->createTable();
    }

    /**
     * @test
     */
    public function shouldBeAbleToCheckTableExistence(){
        $this->dropTable();
        $table = new TestTable();
        $this->assertFalse($table->exists());
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfTableExists(){
        $this->dropTable();
        $table = new TestTable();
        $table->createTable();
        $this->assertTrue($table->exists());
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateNewRecord(){
        $table = new TestTable();
        // when
        $table->setFirstName('John');
        $table->commit();

        // then
        $this->assertNotNull($table->getId());
        $this->assertEquals('John', $table->getFirstName());


    }

    /**
     * @test
     */
    public function shouldBeAbleToDropTable(){
        // given
        $tbl = new TestTable();
        $tbl->drop();

        // then
        $this->assertFalse($tbl->exists());
    }

    /**
     * @test
     */
    public function shouldReturnUpdatesInGetters(){
        // given
        $table = new TestTable();
        $table->setFirstName('John');
        $table->commit();

        // when
        $table->setFirstname('Jane');
        $updates = $table->getUpdates();
        // then

        $this->assertEquals('Jane', $updates['firstname'], "Updates are wrong");
        $this->assertEquals('Jane', $table->getFirstname(), "getter is wrong");
    }

    /**
     * @test
     */
    public function testShouldBeAbleToGetRecordById(){
        $car = new Car();
        $car->drop();
        $car->createTable();
        // when
        $car = new Car(1);
        // then
        $this->assertEquals('Opel', $car->getBrand());
        $this->assertEquals('1', $car->getId());
    }

    /**
     * @test
     */
    public function shouldBeAbleToRollbackUpdates(){
        // given
        $table = new TestTable();
        $table->setFirstName('Jane');
        $table->commit();

        // when
        $table->setFirstname('John');
        $this->assertEquals('John', $table->getFirstname());
        $table->rollback();

        // then
        $this->assertEquals('Jane', $table->getFirstname());
    }

    /**
     * @test
     */
    public function shouldReturnNullWhenNotFound(){
        // given
        $table = new TestTable("Charles");

        // then
        $this->assertNull($table->getFirstname());
    }

    /**
     * @test
     */
    public function shouldBeAbleToUpdateExistingRow(){
        // given
        $table = $this->getExistingRecord();
        $id = $table->getId();
        $table->setFirstname('Harry');
        $table->setLastname('Johnson');
        $table->commit();

        // then
        $this->assertEquals('Harry', $table->getFirstname());

        // when
        $newInstance = new TestTable($table->getId());
        // then
        $this->assertEquals($id, $table->getId());
        $this->assertEquals('Harry', $newInstance->getFirstname());
        $this->assertEquals('Johnson', $newInstance->getLastname());

    }

    /**
     * @test
     */
    public function shouldBeAbleToHaveJoins(){
        // given
        $person = new Person();
        if(!$person->exists())$person->createTable();
        $person->deleteAll();
        $person->setFirstname('John');
        $person->setZip('4330');
        $person->commit();
        $id = $person->getId();

        $city = new City();
        if(!$city->exists())$city->createTable();
        $city->deleteAll();
        $city->setZip(4330);
        $city->setCity('Algard');
        $city->commit();

        // when
        $person = new Person($id);

        // then
        $this->assertEquals($id, $person->getId());
        $this->assertEquals('4330', $person->getZip());
        $this->assertEquals('Algard', $person->getCity());
    }

    /**
     * @test
     */
    public function shouldBeAbleToHaveCollections(){
        $countryId = $this->createCountryData();

        // given
        $country = new Country($countryId);
        $this->assertEquals('Norway', $country->getName());

        // when
        $cities = $country->getCollection('cities');

        // then
        $this->assertEquals(4, count($cities));
    }

    /**
     * @test
     */
    public function shouldBeAbleToDefineDefaultData(){
        // given
        $car = new Car();
        if($car->exists())$car->drop();
        $car->createTable();

        new Car();

        $res = mysql_query("select count(id) as num from car");
        $row = mysql_fetch_assoc($res);

        // then
        $this->assertEquals(7, $row['num']);

        $car = new Car(1);
        $this->assertEquals('Opel', $car->getBrand());

    }

    private function createCountryData(){
        $country = new Country();
        if(!$country->exists())$country->createTable();
        $country->setName('Norway');
        $country->commit();
        $id = $country->getId();

        $cities = array('Oslo','Bergen','Stavanger','Sandnes');
        $city = new City();
        if($city->exists())$city->drop();
        $city->createTable();
        $i=0;
        foreach($cities as $cityName){
            $city = new City();
            $city->setCity($cityName);
            $city->setZip(5000 + $i);
            $city->setCountryId($id);
            $city->commit();
            $i++;
        }
        return $country->getId();
    }

    private function getExistingRecord(){
        $this->clearTable();
        $table = new TestTable();
        $table->setFirstname('Jane');
        $table->setLastname('Doe');
        $table->commit();
        return $table;
    }

}
