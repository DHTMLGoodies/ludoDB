<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13
 * Time: 15:13
 * To change this template use File | Settings | File Templates.
 */

require_once(__DIR__ . "/../autoload.php");

class ConfigParserTestJSON extends TestBase
{

    /**
     * @test
     */
    public function shouldBeAbleToReadConfigAsJSON(){
        // given
        $client = new Client();

        // when
        $table = $client->configParser()->getTableName();

        // then
        $this->assertEquals("Client", $table);
    }
}
