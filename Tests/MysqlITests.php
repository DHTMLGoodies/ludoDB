<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 11.01.13


 */
require_once(__DIR__ . "/../autoload.php");

class MySqlITests extends TestBase
{

    private static $connectionSet = false;

    public function setUp()
    {
        parent::setUp();
        if(!self::$connectionSet){
            LudoDb::setConnectionType('MYSQLI');
        }
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateAndGetObjects()
    {
        // given
        $person = new Person();
        $person->setFirstname('Alf');
        $person->commit();

        // when
        $p = new Person($person->getId());

        // then
        $this->assertEquals('Alf', $p->getFirstname());
    }

}
