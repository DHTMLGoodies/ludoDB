<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 22.12.12

 */

require_once(__DIR__ . "/../autoload.php");

class SQLTest extends TestBase{

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
    public function shouldGetCreateSqlIncludingDefaultValues(){
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => 'varchar(32)',
                'lastname' => array(
                    'db' => 'varchar(32)',
                    'default' => 'Doe'
                )
            )
        );
        // when
        $expected ="create table Person(firstname varchar(32),lastname varchar(32) default ?)";
        $sql = $this->getSqlObject($config)->getCreateTableSql();
        // then
        $this->assertEquals($expected, $sql);

    }

    /**
     * @test
     */
    public function shouldGetTableCreationSqlWithReferences(){
        // given
        $config = array(
            'table' => 'Person',
            'columns' => array(
                'firstname' => array(
                    'db' => 'varchar(32)',
                    'references' => 'man(firstname) on delete cascade'
                ),
                'lastname' => 'varchar(32)'
            )
        );
        // when
        $expected ="create table Person(firstname varchar(32),FOREIGN KEY(firstname) REFERENCES man(firstname) on delete cascade,lastname varchar(32))";
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
            'constructBy' => 'id'
        );

        // when
        $sql = $this->getSqlObject($config, 1)->getSql();
        $expected = "select Person.firstname,Person.lastname from Person where Person.id=?";

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
            'constructBy' => array('id','city'),
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
        $expected = "select Person.firstname,Person.lastname,Person.zip,City.city from Person,City where Person.zip=City.zip and Person.id=? and Person.city=?";

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
            'constructBy' => 'id'
        );

        // when
        $sql = $this->getSqlObject($config, 1)->getSql();
        $expected = "select Person.firstname,Person.lastname from Person where Person.id=?";

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
            'constructBy' => 'Person.id',
            'join' => array(
                array('table' => 'City', 'columns' => array('city'), 'fk' => 'zip', 'pk' => 'zip')
            )
        );

        // when
        $sql = $this->getSqlObject($config, 1)->getSql();
        $expected = "select Person.firstname,Person.lastname,Person.zip,City.city from Person,City where Person.zip=City.zip and Person.id=?";

        // then
        $this->assertEquals($expected, $sql);
    }

    /**
     * @test
     */
    public function shouldBeAbleToHaveSQLDefinedInConfig(){
        // given
        $config = array(
            'sql' => "select id,firstname,lastname from person where ID=? and zip=?"
        );

        // when
        $sql = $this->getSqlObject($config, array(1,4330))->getSql();
        $expectedSql = "select id,firstname,lastname from person where ID=? and zip=?";
        // then
        $this->assertEquals($expectedSql, $sql);

    }

    /**
     * @test
     */
    public function shouldBeAbleToDefineMethodForSql(){
        // given
        $obj = new ModelWithSqlMethod(1);

        // when
        $sql = $obj->sqlHandler()->getSql();

        // then
        $this->assertEquals("select * from person where id=?", $sql);
    }

    /**
     * @test
     */
    public function shouldBeAbleToDifferentSqlsBasedOnArguments(){
        // given
        $obj = new ModelWithSqlMethod('John');

        // when
        $sql = $obj->sqlHandler()->getSql();

        // then
        $this->assertEquals("select * from person where firstname=?", $sql);
    }


    private function getSqlObject($config, $constructBy = null)
    {
        ForSQLTest::clearParsers();
        $obj = new ForSQLTest();
        $obj->setConfig($config);
        $obj->setConstructorValues($constructBy);

        return new LudoDBSql($obj);

    }

}
