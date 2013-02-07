<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13


 */
require_once(__DIR__ . "/../autoload.php");

class AccessorTest extends TestBase
{

    public function setUp(){
        parent::setUp();
        $g = new TestGame();
        $g->drop()->yesImSure();
        $g->createTable();
    }
    /**
     * @test
     */
    public function shouldBeAbleToCreateDynamicSetters(){
        // given
        $game = new TestGame();

        // when
        $game->setDatabaseId(1);
        $unCommitted = $game->getUncommitted();
        // then
        $this->assertEquals(1, $unCommitted['databaseId']);
        $this->assertEquals(1, $game->getDatabaseId());
    }/**
     * @test
     */
    public function shouldBeAbleToCreateDynamicGetters(){
        // given
        $game = new TestGame();

        // when
        $game->setPlayerId(1);

        // then
        $this->assertEquals(1, $game->getPlayerId());
    }

    /**
     * @test
     */
    public function shouldKeepValuesAfterCommit(){
        // given
        $game = new TestGame();

        // when
        $game->setDatabaseId(1);
        $game->setPlayerId(100);
        $game->commit();

        $newGame = new TestGame($game->getId());

        // then
        $this->assertEquals(1, $newGame->getDatabaseId());
        $this->assertEquals(100, $newGame->getPlayerId());

    }
}
