<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13


 */
require_once(__DIR__ . "/../autoload.php");

class ConfigParserTest extends TestBase
{
    public function setUp(){
        parent::setUp();
    }
    /**
     * @test
     */
    public function shouldFindTableName(){
        // given
        $person = new PersonForConfigParser();

        // when
        $tableName = $person->configParser()->getTableName();

        // then
        $this->assertEquals('Person', $tableName);
    }

    /**
     * @test
     */
    public function shouldGetConstructorFields(){
        // given
        $person = new PersonForConfigParser();

        // when
        $configParams = $person->configParser()->getConstructorParams();
        $expected = array('id');
        // then

        $this->assertEquals($expected, $configParams);
    }

    /**
     * @test
     */
    public function shouldGetColumnProperties(){
        // given
        $person = new PersonForConfigParser();

        // when
        $className = $person->configParser()->externalClassNameFor('city');

        // then
        $this->assertEquals('City', $className);
    }

    /**
     * @test
     */
    public function shouldFindExternalColumns(){
         // given
        $person = new PersonForConfigParser();

        // then
        $this->assertTrue($person->configParser()->isExternalColumn('city'));
        $this->assertFalse($person->configParser()->isExternalColumn('firstname'));
        $this->assertFalse($person->configParser()->isExternalColumn('address'));
    }

    /**
     * @test
     */
    public function shouldFindIdField(){
         // given
        $person = new PersonForConfigParser();

        // then
        $this->assertEquals('id', $person->configParser()->getIdField());
    }

    /**
     * @test
     */
    public function shouldFindIfIdIsAutoIncremented(){
         // given
        $person = new PersonForConfigParser();

        // then
        $this->assertTrue($person->configParser()->isIdAutoIncremented());
    }

    /**
     * @test
     */
    public function shouldFindSetMethodForAColumn(){
         // given
        $person = new PersonForConfigParser();

        // when
        $method = $person->configParser()->getSetMethod('city');

        // then
        $this->assertEquals('setCity', $method);
    }

    /**
     * @test
     */
    public function shouldGetExternalClassProperties(){
         // given

        $person = new PersonForConfigParser();

        // when
        $foreignKey = $person->configParser()->foreignKeyFor('city');

        // then
        $this->assertEquals('zip', $foreignKey);
    }

    /**
     * @test
     */
    public function shouldGetColumnByMethodName(){
        // given
        $person = new PersonForConfigParser();

        // when
        $col = $person->configParser()->getColumnByMethod('setLastname');

        // then
        $this->assertEquals('lastname', $col);
        // when
        $col = $person->configParser()->getColumnByMethod('setAreaCode');

        // then
        $this->assertEquals('area_code', $col);

        // given
        $game = new TestGame();

        // when
        $col = $game->configParser()->getColumnByMethod('setDatabaseId');

        // then
        $this->assertEquals('databaseId', $col);
    }

    /**
     * @test
     */
    public function shouldGetColumnNameByMethodNameUsingCache(){
         // given
        $game = new TestGame();

        // when
        $col = $game->configParser()->getColumnByMethod('setDatabaseId');

        // then
        $this->assertEquals('databaseId', $col);

                // given
        $game = new TestGame();

        // when
        $col = $game->configParser()->getColumnByMethod('setDatabaseId');

        // then
        $this->assertEquals('databaseId', $col);

    }

    /**
     * @test
     */
    public function shouldFindColumnsWithWriteAccess(){
        // given
        $person = new PersonForConfigParser();

        // when
        $access = $person->configParser()->canWriteTo('address');

        // then
        $this->assertTrue($access);

    }
    /**
     * @test
     */
    public function shouldFindColumnsWithReadAccess(){
        // given
        $person = new PersonForConfigParser();

        // when
        $access = $person->configParser()->canReadFrom('address');

        // then
        $this->assertFalse($access);
        // when
        $access = $person->configParser()->canReadFrom('area_code');

        // then
        $this->assertTrue($access);

    }

    /**
     * @test
     */

    public function shouldBeAbleToReadId(){
        // given
        $person = new PersonForConfigParser();

        // when
        $access = $person->configParser()->canReadFrom('id');

        // then
        $this->assertTrue($access);
    }

    /**
     * @test
     */
    public function shouldBeAbleToExtendConfig(){
        // given
        $manager = new Manager();

        // when
        $columns = $manager->configParser()->getColumns();
        // then
        $this->assertTrue(isset($columns['address']));
        $this->assertEquals('varchar(10)', $columns['zip']);
        $this->assertEquals("Manager", $manager->configParser()->getTableName());
    }

    /**
     * @test
     */
    public function shouldGetReferencesToOtherTables(){
        // given
        $obj = new AChild();

        // when
        $references = $obj->configParser()->getTableReferences();

        // then
        $this->assertEquals(2, count($references));
        $this->assertEquals("a_parent", $references[0]['table']);
        $this->assertEquals("a_city", $references[1]['table']);

        $this->assertEquals("id", $references[0]['column']);
        $this->assertEquals("zip", $references[1]['column']);
    }

    /**
     * @test
     */
    public function shouldFindDefaultValues(){
        // given
        $p = new Person();

        // then
        $this->assertEquals('female', $p->configParser()->getDefaultValue('sex'));
    }
}
