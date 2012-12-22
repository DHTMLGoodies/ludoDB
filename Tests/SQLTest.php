<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 22.12.12
 * Time: 00:29
 */

require_once(__DIR__ . "/../autoload.php");

class SQLTest extends TestBase
{
    /**
     * @test
     */
    public function shouldParseWithoutJoins()
    {
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => 'varchar(32)'
            )
        );

        // when
        $sql = $this->getSQL($config);
        $expected = "select Person.firstname,Person.lastname from Person";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldParseSimpleColumnArray(){
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname', 'lastname'
            )
        );

        // when
        $sql = $this->getSQL($config);
        $expected = "select Person.firstname,Person.lastname from Person";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldBeAbleToApplyWhereClause()
    {
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => 'varchar(32)'
            ),
            'lookupField' => 'id'
        );

        // when
        $sql = $this->getSQL($config, 1);
        $expected = "select Person.firstname,Person.lastname from Person where Person.id='1'";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldNotSelectColumnsFromExternal()
    {
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => 'varchar(32)',
                'phone' => array(
                    'class' => 'PhoneCollection'
                )
            ),
            'lookupField' => 'id'
        );

        // when
        $sql = $this->getSQL($config, 1);
        $expected = "select Person.firstname,Person.lastname from Person where Person.id='1'";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldParseJoins(){
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => 'varchar(32)',
                'zip' => 'varchar(15)'
            ),
            'lookupField' => 'Person.id',
            'join' => array(
                array('table' => 'City', 'columns' => array('city'), 'fk' => 'zip', 'pk' => 'zip')
            )
        );

        // when
        $sql = $this->getSQL($config, 1);
        $expected = "select Person.firstname,Person.lastname,Person.zip,City.city from Person,City where Person.zip = City.zip and Person.id='1'";

        // then
        $this->assertEquals($expected, $sql);
    }

    private function getSQL($config, $lookupField = null)
    {
        $sql = new LudoSQL($config, $lookupField);
        return $sql->getSql();
    }
}
