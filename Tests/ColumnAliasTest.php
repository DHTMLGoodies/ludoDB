<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 12.01.13

 */
require_once(__DIR__ . "/../autoload.php");

class ColumnAliasTest extends TestBase
{
    public function setUp(){
        parent::setUp();

        $s = new Section();
        $s->drop()->yesImSure();
        $s->createTable();
    }


    /**
     * @test
     */
    public function shouldBeAbleToGetColumnValueUsingAlias(){
        // given
        $section = new Section();

        // when
        $col = $section->configParser()->getColumn('writtenBy');

        // then
        $this->assertNotNull($col);

    }

    /**
     * @test
     */
    public function shouldReturnAliasNameInGetValuesWhenSet(){
        // given
        $section = new Section();
        $section->setWrittenBy(100);
        $section->commit();

        $this->assertEquals(100, $section->getWrittenBy());

        // when
        $values = json_decode($section, true);

        // then
        $this->assertNotNull($values['writtenBy']);
        $this->assertArrayNotHasKey('written_by', $values);
        $this->assertEquals(100, $values['writtenBy']);

    }
}
