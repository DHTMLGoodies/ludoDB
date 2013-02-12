<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 12.02.13
 * Time: 21:28
 */
require_once(__DIR__."/../autoload.php");

class AvailableServicesTest extends TestBase
{
    /**
     * @test
     */
    public function shouldBeAbleToRegisterService(){
        // given
        LudoDB::registerService('Person');

        // when
        $services = LudoDB::getAllServices();

        // then
        $this->assertNotNull($services['Person']);
        $this->assertEquals(array('save','delete','read'), $services['Person']);
    }

}
