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
 * Time: 08:58
 */
class LudoDBUtilityTest extends TestBase
{

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
}
