<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13


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
