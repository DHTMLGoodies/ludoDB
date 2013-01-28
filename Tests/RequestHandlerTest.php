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

    private $persons = array(
        array('f' => 'Jane', 'l' => 'Wayne', 'zip' => 1003),
        array('f' => 'John', 'l' => 'Anderson', 'zip' => 1004),
        array('f' => 'Mike', 'l' => 'Johnson', 'zip' => 1005),
        array('f' => 'Katy', 'l' => 'Peterson', 'zip' => 1006),

    );

    public function setUp()
    {
        parent::setUp();

        $person = new Person();
        $person->drop()->yesImSure();
        $person->createTable();

        foreach ($this->persons as $person) {
            $p = new Person();
            $p->setFirstname($person['f']);
            $p->setLastname($person['l']);
            $p->setZip($person['zip']);
            $p->commit();
        }


    }

    /**
     * @test
     */
    public function shouldHandleSimplePosts()
    {
        // given
        $data = array(
            'model' => 'Person',
            'method' => 'save',
            'data' => array(
                'firstname' => 'John',
                'lastname' => 'Wayne'
            )
        );

        // when
        $returned = new LudoRequestHandler($data);
    }
}
