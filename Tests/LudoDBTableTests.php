<?php
/**
 * PHPUnit tests ludoDB
 * User: Alf Magne Kalleland
 * Date: 04.11.12
 * Time: 02:15
 */
require_once(__DIR__ . "/../autoload.php");
#error_reporting(E_ALL);
ini_set('display_errors','on');

class LudoDBTableTests extends TestBase
{
    public function setUp(){
        parent::setUp();

        $city = new City();
        $city->drop();
        $city->createTable();

        $phone = new Phone();
        if(!$phone->exists())$phone->createTable();
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
    public function shouldCaptureQueryValues(){
        // when
        $city = new City(1);

        // then
        $this->assertEquals(1, count($city->getConstructorValues()));
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
        $this->assertEquals(1, count($table->getUncommitted()));
        $this->assertEquals(array('firstname' => 'Alf Magne'), $table->getUncommitted());
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
        $updates = $table->getUncommitted();
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
        $person->deleteTableData();
        $person->setFirstname('John');
        $person->setZip('4330');
        $person->commit();
        $id = $person->getId();

        $city = new City();
        if(!$city->exists())$city->createTable();
        $city->deleteTableData();
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
    public function shouldBeAbleToDefineDefaultData(){
        // given
        $car = new Car();
        if($car->exists())$car->drop();
        $car->createTable();

        new Car();

        $db = new LudoDB();
        $row = $db->one("select count(id) as num from car");

        // then
        $this->assertEquals(7, $row['num']);

        $car = new Car(1);
        $this->assertEquals('Opel', $car->getBrand());

    }

    /**
     * @test
     */
    public function shouldBeAbleToDeleteColumnValue(){
        // given
        $person = new Person();
        $person->setFirstname('John');
        $person->setLastname('Wayne');
        $city = new City();
        $city->setZip('8642');
        $city->commit();

        $person->setZip('8642');
        $person->commit();
        $id = $person->getId();

        $this->assertEquals('John', $person->getFirstname(), 'Initial first name');
        $this->assertEquals('Wayne', $person->getLastname(), 'Initial last name');

        $person->setLastname(null);
        $person->commit();
        $secondId = $person->getId();

        $this->assertEquals($id, $secondId);
        // when
        $newPerson = new Person($person->getId());


        // then
        $this->assertNotNull($secondId);
        $this->assertNull($person->getUncommitted());
        $this->assertEquals('8642', $newPerson->getZip());
        $this->assertEquals('John', $newPerson->getFirstname());
        $this->assertNull($newPerson->getLastname());
    }

    /**
     * @test
     */
    public function collectionShouldReturnEmptyValueForNewObjects(){
        // given
        $person = new Person();

        // then
        $this->assertEquals(0, count($person->getPhone()));
    }

    /**
     * @test
     */
    public function shouldSetIdOnCommit(){
        $person = new Person();
        $person->setFirstname('Alf');
        $person->commit();

        // then
        $this->assertNotNull($person->getId());
    }

    /**
     * @test
     */
    public function shouldGetValueFromJoinsOnNewObjects(){
        $person = new Person();
        $person->setZip('7001');
        $city = new City();
        $city->setZip('7001');
        $city->setCity('Somewhere');
        $city->commit();

        // then
        $this->assertEquals('Somewhere', $person->getCity());

    }

    /**
     * @test
     */
    public function shouldBeAbleToGetReferenceToColumn(){
        // given
        $person = new Person();

        // when
        $col = $person->getColumn('phone');

        // then
        $this->assertEquals('PhoneCollection', get_class($col));


    }

    /**
     * @test
     */
    public function shouldBeAbleToSaveNewWhenThereAreNoUpdates(){
        // given
        $person = new Person();

        // when
        $person->commit();

        // then
        $this->assertNotNull($person->getId());
    }

    /**
     * @test
     */
    public function shouldBeAbleToSetMultipleValues(){
        // given
        $p = new Person();
        $data = array(
            'firstname' => 'John',
            'lastname' => 'Wayne'
        );

        // when
        $p->setValues($data);
        $p->commit();
        $person = new Person($p->getId());

        // then
        $this->assertEquals('John', $person->getFirstname());
        $this->assertEquals('Wayne', $person->getLastname());
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
