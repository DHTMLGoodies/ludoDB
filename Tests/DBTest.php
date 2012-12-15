<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 04.11.12
 * Time: 02:15
 * To change this template use File | Settings | File Templates.
 */
error_reporting(E_ALL);
ini_set('display_errors','on');
require_once(__DIR__ . "/../autoload.php");

class DBTest extends PHPUnit_Framework_TestCase
{
    private $connected = false;
    public function setUp(){
        if(!$this->connected)$this->connect();

        $this->dropTable();
        $tbl = new TestTable();
        $tbl->construct();
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
    public function shouldBeAbleToInstantiateByName(){
        // given
        $table = new TestTable();
        $table->setFirstname('Mike');
        $table->commit();

        // when
        $table2 = new TestTable("Mike");

        // then
        $this->assertEquals('Mike', $table2->getFirstname());
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateTable(){
        $this->dropTable();
        $table = new TestTable();
        $table->construct();
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
        $table->construct();
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
    public function testShouldBeAbleToGetByLikeFields(){
        // given
        $table = new TestTable();
        $table->setFirstName('Jeff');
        $table->setLastname('Jones');
        $table->commit();

        // when
        $table = new TestTable('Jone');

        // then
        $this->assertEquals('Jones', $table->getLastname());
        $this->assertEquals('Jeff', $table->getFirstname());
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
        if(!$person->exists())$person->construct();
        $person->deleteAll();
        $person->setId('1');
        $person->setFirstname('John');
        $person->setZip('4330');
        $person->commit();
        $city = new City();
        if(!$city->exists())$city->construct();
        $city->deleteAll();
        $city->setZip(4330);
        $city->setCity('Algard');
        $city->commit();

        // when
        $person = new Person(1);

        // then
        $this->assertEquals('Algard', $person->getCity());


    }

    private function getExistingRecord(){
        $this->clearTable();
        $table = new TestTable();
        $table->setFirstname('Jane');
        $table->setLastname('Doe');
        $table->commit();
        return $table;
    }

    private function clearTable(){
        mysql_query("delete from TestTable");
    }
    private function dropTable(){
        mysql_query("drop table TestTable");
    }

    private function connect(){
        $res = mysql_connect("localhost", "root", "Kaffien2");
        mysql_select_db('test', $res);

        $this->connected = true;
    }
}
