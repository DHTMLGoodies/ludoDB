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

    private $getRequest = array(
        'model' => 'Person',
        'action' => 'read',
        'data' => array(1)
    );

    private $createRequest = array(
        'model' => 'Person',
        'action' => 'create',
        'data' => array(
            'firstname' => 'Alf Magne',
            'lastname' => 'Kalleland'
        )
    );

    private $updateRequest = array(
        'model' => 'Person',
        'update' => 2,
        'data' => array(
            'firstname' => 'Andrea'
        )
    );

    private $deleteRequest = array(
        'model' => 'Person',
        'delete'=> array(1)
    );

    public function setUp()
    {
        parent::setUp();
        $this->createPersons();
    }

    private function createPersons(){
        $person = new Person();
        $person->drop()->yesImSure();
        $person->createTable();

        $persons = array(
            array('f' => 'Jane', 'l' => 'Wayne', 'zip' => 1003),
            array('f' => 'John', 'l' => 'Anderson', 'zip' => 1004),
            array('f' => 'Mike', 'l' => 'Johnson', 'zip' => 1005),
            array('f' => 'Katy', 'l' => 'Peterson', 'zip' => 1006),
        );

        foreach ($persons as $person) {
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
    public function shouldFindCRUDMethod(){

        // given
        $handler = new RequestHandlerMock($this->getRequest);

        // when
        $crud = $handler->getCRUDAction();

        // then
        $this->assertEquals('R', $crud);
    }

    /**
     * @test
     */
    public function shouldHandleSimplePosts()
    {
        // given
        $request = $this->getRequest;

        // when
        $returned = new RequestHandlerMock($request);
        $asArray = json_decode($returned, true);

        // then
        $this->assertEquals('Jane', $asArray['firstname']);
    }
}
