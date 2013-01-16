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
    public function shouldGetTableCreationSQL(){
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => 'varchar(32)'
            )
        );
        // when
        $expected ="create table Person(firstname varchar(32),lastname varchar(32))";
        $sql = $this->getSqlObject($config)->getCreateTableSql();
        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldGetTableCreationSQLWhenDefIsInArray(){
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => array(
                    'db' => 'varchar(32)'
                )
            )
        );
        // when
        $expected ="create table Person(firstname varchar(32),lastname varchar(32))";
        $sql = $this->getSqlObject($config)->getCreateTableSql();
        // then
        $this->assertEquals($expected, $sql);
    }


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
        $sql = $this->getSqlObject($config)->getSql();
        $expected = "select Person.firstname,Person.lastname from Person";

        // then
        $this->assertEquals($expected, $sql);
    }



    /**
     * @test
     */
    public function shouldParseSimpleColumnArray()
    {
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname', 'lastname'
            )
        );

        // when
        $sql = $this->getSqlObject($config)->getSql();
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
            'constructorParams' => 'id'
        );

        // when
        $sql = $this->getSqlObject($config, 1)->getSql();
        $expected = "select Person.firstname,Person.lastname from Person where Person.id='1'";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldBeAbleToApplyMultipleValuesToWhereClause()
    {
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => 'varchar(32)',
                'zip' => 'varchar(10)'
            ),
            'constructorParams' => array('id','city'),
            'join' => array(
                array(
                    'table' => 'City',
                    'fk' => 'zip',
                    'pk' => 'zip',
                    'columns' => array('city')
                )
            )
        );

        // when
        $sql = $this->getSqlObject($config, array(1,'Stavanger'))->getSql();
        $expected = "select Person.firstname,Person.lastname,Person.zip,City.city from Person,City where Person.zip=City.zip and Person.id='1' and Person.city='Stavanger'";

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
            'constructorParams' => 'id'
        );

        // when
        $sql = $this->getSqlObject($config, 1)->getSql();
        $expected = "select Person.firstname,Person.lastname from Person where Person.id='1'";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldParseJoins()
    {
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => 'varchar(32)',
                'zip' => 'varchar(15)'
            ),
            'constructorParams' => 'Person.id',
            'join' => array(
                array('table' => 'City', 'columns' => array('city'), 'fk' => 'zip', 'pk' => 'zip')
            )
        );

        // when
        $sql = $this->getSqlObject($config, 1)->getSql();
        $expected = "select Person.firstname,Person.lastname,Person.zip,City.city from Person,City where Person.zip=City.zip and Person.id='1'";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldBeAbleToHaveSQLDefinedInConfig(){
        // given
        $config = array(
            'sql' => "select id,firstname,lastname from person where ID='?' and zip='?'"
        );

        // when
        $sql = $this->getSqlObject($config, array(1,4330))->getSql();
        $expectedSql = "select id,firstname,lastname from person where ID='1' and zip='4330'";
        // then
        $this->assertEquals($expectedSql, $sql);

    }


    private function getSqlObject($config, $constructorParams = null)
    {
        ForSQLTest::clearParsers();
        $obj = new ForSQLTest();
        $obj->setConfig($config);
        $obj->setConstructorValues($constructorParams);

        return new LudoSQL($obj);

    }

}
