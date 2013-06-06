<?php

require_once(__DIR__."/../autoload.php");

class LudoDBUtilityMock extends LudoDBUtility{

    public function getLudoDBModelTables($classNames){
        return parent::getLudoDBModelTables($classNames);
    }

    public function getClassesRearranged($classNames){
        return parent::getClassesRearranged($classNames);
    }
}
/**
 *
 * User: Alf Magne
 * Date: 06.02.13

 */
class LudoDBUtilityTest extends TestBase
{

    public function setUp(){
        parent::setUp();

        $p = new PersonForUtility();
        if($p->exists())$p->drop()->yesImSure();
        $p->createTable();
    }
    /**
     * @test
     */
    public function shouldOnlyGetTablesOfSubClassLudoDBModel(){
        // given
        $classes = array('AChild','ASibling','LudoDBModel','GrandParent','AParent','NoLudoDBClass');
        $util = new LudoDBUtilityMock();
        // when
        $ludoDBTables = $util->getLudoDBModelTables($classes);
        $expected = array('AChild','ASibling','GrandParent','AParent');

        // then
        $this->assertEquals(4, count($ludoDBTables));
        $this->assertEquals($expected, $ludoDBTables);
    }

    /**
     * @test
     */
    public function shouldGetTablesReArranged(){
        // given
        $classes = array('AChild','ASibling','LudoDBModel','GrandParent','AParent','NoLudoDBClass','ACity');
        $util = new LudoDBUtilityMock();
        // when
        $ludoDBTables = $util->getClassesRearranged($classes);
        $expected = array('ACity','GrandParent','AParent','ASibling','AChild');

        // then
        $this->assertEquals(5, count($ludoDBTables));
        $this->assertEquals($expected, $ludoDBTables);
    }

    /**
     * @test
     */
    public function shouldExcludeDuplicates(){
        // given
        $classes = array('AChild','ASibling','LudoDBModel','GrandParent','AParent','NoLudoDBClass','ACity','AChild');
        $util = new LudoDBUtilityMock();
        // when
        $ludoDBTables = $util->getClassesRearranged($classes);
        $expected = array('ACity','GrandParent','AParent','ASibling','AChild');

        // then
        $this->assertEquals(5, count($ludoDBTables));
        $this->assertEquals($expected, $ludoDBTables);
    }

    /**
     * @test
     */
    public function shouldFindUpdatedColumns(){
        // given
        $obj = new PersonForUtility();
        $tableName = $obj->configParser()->getTableName();

        $tableDef = LudoDB::getInstance()->getTableDefinition($tableName);

        $this->assertArrayHasKey("id", $tableDef, json_encode($tableDef));
        $this->assertArrayHasKey("firstname", $tableDef);
        $this->assertArrayHasKey("zip", $tableDef);
    }
}
