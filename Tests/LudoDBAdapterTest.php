<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 04:43
 */
require_once(__DIR__ . "/../autoload.php");

class LudoDBAdapterTest extends TestBase
{
    /**
     * @test
     */
    public function shouldDetermineDatabaseExistence(){
        $this->assertFalse(LudoDB::getInstance()->databaseExists('abcdefg'));
        $this->assertTrue(LudoDB::getInstance()->databaseExists('PHPUnit'));
    }
}
