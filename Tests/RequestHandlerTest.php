<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 13.01.13
 * Time: 16:25
 */

require_once(__DIR__ . "/../autoload.php");

class RequestHandlerTest extends TestBase
{

    /**
     * @test
     */
    public function shouldHandleSimplePosts(){
        // given
        $data = array(
            'model' => 'Person',
            'firstname' => 'John',
            'lastname' => 'Wayne'
        );

        // when
        $returned = new LudoRequestHandler($data);
    }
}
