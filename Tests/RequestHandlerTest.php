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
        'request' => 'Person/1/read',
        'data' => array(1)
    );

    private $createRequest = array(
        'request' => 'Person/create',
        'data' => array(
            'firstname' => 'Alf Magne',
            'lastname' => 'Kalleland'
        )
    );

    private $updateRequest = array(
        'request' => 'Person/2/save',
        'data' => array(
            'firstname' => 'Andrea'
        )
    );

    private $deleteRequest = array(
        'request' => 'Person/1/delete'
    );

    public function setUp()
    {
        parent::setUp();
        $this->createPersons();
    }

    private function createPersons()
    {
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
    public function shouldFindCRUDMethod()
    {

        // given
        $handler = new RequestHandlerMock();


        // when
        $crud = $handler->getAction($this->getRequest);

        // then
        $this->assertEquals('read', $crud);
    }

    /**
     * @test
     */
    public function shouldFindArguments()
    {
        // given
        $handler = new RequestHandlerMock();

        $request = array('request' => 'Person/1');

        // when

        $args = $handler->getArguments($request);

        // then
        $this->assertEquals(array(1), $args);

        // given
        $request = array('request' => 'Person/1/2');

        // when

        $args = $handler->getArguments($request);

        // then
        $this->assertEquals(array(1, 2), $args);

        // given
        $request = array('request' => 'Person/1/2/read');

        // when
        $args = $handler->getArguments($request);

        // then
        $this->assertEquals(array(1, 2), $args);

        // given
        $request = array('request' => 'Person/1/2/save');

        // when
        $args = $handler->getArguments($request);

        // then
        $this->assertEquals(array(1, 2), $args);

        // given
        $request = array('request' => 'Person/1/2/delete');

        // when
        $args = $handler->getArguments($request);

        // then
        $this->assertEquals(array(1, 2), $args);
    }

    /**
     * @test
     */
    public function shouldFindLudoDBObject()
    {
        // given
        $handler = new RequestHandlerMock();

        // when
        $model = $handler->getModel($this->getRequest);

        // then
        $this->assertInstanceOf('Person', $model);
    }

    /**
     * @test
     */
    public function shouldHandleSimpleGetRequests()
    {
        // given
        $request = $this->getRequest;

        // when
        $returned = new RequestHandlerMock();
        $asArray = json_decode($returned->handle($request), true);

        // then
        $this->assertEquals('Jane', $asArray['response']['firstname']);
    }

    /**
     * @test
     */
    public function shouldHandleUpdateRequests()
    {
        // given
        $request = array(
            'request' => 'Person/2/save',
            'data' => array(
                'firstname' => 'Andrea'
            )
        );
        // when
        $handler = new RequestHandlerMock();
        $handler->handle($request);
        $person = new Person(2);

        // then
        $this->assertEquals('Andrea', $person->getFirstname());
    }



}
