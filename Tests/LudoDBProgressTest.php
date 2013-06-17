<?php
/**
 *
 * User: Alf Magne
 * Date: 17.06.13
 * Time: 14:30
 */

require_once(__DIR__ . "/../autoload.php");

class LudoDBProgressTest extends TestBase
{
    /**
     * @test
     */
    public function shouldCreateViaRequestHandler()
    {

        $id = $this->getUniqueProgressId();

        // given
        $handler = new RequestHandlerMock();


        // when
        $handler->handle("Car/1/read", array(
            'LudoDBProgressID' => $id

        ));
        $progress = new LudoDBProgress($id);

        // then
        $this->assertEquals($id, $progress->getId());
    }

    /**
     * @test
     */
    public function shouldRemoveProgressInfoFromRequest()
    {

        // given
        $handler = new RequestHandlerMock();


        // when
        $json = $handler->handle("Car/1/read", array(
            'LudoDBProgressID' => $this->getUniqueProgressId()
        ));
        $response = JSON_decode($json, true);
        $data = $response['response'];
        // then
        $this->assertTrue($response['success'], $json);
        $this->assertNotNull($data['brand'], $json);
        $this->assertEquals('Opel', $data['brand'], $json);
    }

    private function getUniqueProgressId(){
        return 'abcd' . microtime(true).rand(0,15000);
    }

}
