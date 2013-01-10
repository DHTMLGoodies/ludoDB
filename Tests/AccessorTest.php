<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13
 * Time: 15:52
 * To change this template use File | Settings | File Templates.
 */
require_once(__DIR__ . "/../autoload.php");

class AccessorTest extends TestBase
{
    /**
     * @test
     */
    public function shouldBeAbleToCreateDynamicSetters(){
        // given
        $game = new Game();

        // when
        $game->setDatabaseId(1);

        // then
        $this->assertEquals(1, $game->getDatabaseId());
    }/**
     * @test
     */
    public function shouldBeAbleToCreateDynamicGetters(){
        // given
        $game = new Game();

        // when
        $game->setPlayerId(1);

        // then
        $this->assertEquals(1, $game->getPlayerId());
    }
}
