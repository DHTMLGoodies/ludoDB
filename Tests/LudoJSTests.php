<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 13.04.13
 * Time: 16:32
 */
require_once(__DIR__ . "/../autoload.php");

class LudoJSTests extends TestBase
{
    public function setUp(){
        parent::setUp();

        $util = new LudoDBUtility();
        $util->dropAndCreate(array("TLudoJSPerson","TLudoJSCountry"));

    }
    /**
     * @test
     */
    public function shouldBeAbleToGetLudoJSConfigFromObject(){
        // given
        $person = new TLudoJSPerson();

        // when
        $ludoJSConfig = $person->configParser()->getLudoJSConfig();

        // then
        $this->assertNotNull($ludoJSConfig);
        $this->assertNotNull($ludoJSConfig['id']);
        $this->assertNotNull($ludoJSConfig['firstname']);

    }

    /**
     * @test
     */
    public function shouldBeAbleToGetLudoJSConfigOfStaticColumns(){
        // given
        $person = new TLudoJSPerson();

        // when
        $ludoJSConfig = $person->configParser()->getLudoJSConfig();

        // then
        $this->assertNotNull($ludoJSConfig['type']);
    }

    /**
     * @test
     */
    public function shouldBeAbleToGetFromRequestHandler(){
        // given
        $handler = new LudoDBRequestHandler();
        // when
        $form = $handler->handle("LudoJS/TLudoJSPerson/form");
        $form = json_decode($form, true);


        // then
        $this->assertNotNull($form['response']['children'], json_encode($form));

        $children = $form['response']['children'];

        $this->assertNotNull($this->getLudoJsFor('firstname', $children));

    }


    /**
     * @test
     */
    public function shouldGetChildrenInRightOrder(){
        // given
        $handler = new LudoDBRequestHandler();
        $form = $handler->handle("LudoJS/TLudoJSPerson/1/form");
        $form = json_decode($form, true);

        //when
        $children = $form['response']['children'];
        $posFirst = $this->getIndexOf('firstname', $children);
        $posLast = $this->getIndexOf('lastname', $children);

        // then
        $this->assertLessThan($posLast, $posFirst);

    }

    /**
     * @test
     */
    public function shouldPopulateDataSourceWhenSetInConfig(){
        // given
        $handler = new LudoDBRequestHandler();
        $form = $handler->handle("LudoJS/TLudoJSPerson/1/form");
        $form = json_decode($form, true);
        $children = $form['response']['children'];

        //when
        $country = $this->getLudoJsFor('country', $children);

        // then
        $this->assertNotNull($country['dataSource']);
        $this->assertEquals(196, count($country['dataSource']['data']));
    }
    /**
     * @test
     */
    public function shouldPopulateWithDefaultValues(){
        // given
        $handler = new LudoDBRequestHandler();
        // when
        $form = $handler->handle("LudoJS/TLudoJSPerson/1/form");
        $form = json_decode($form, true);

        $children = $form['response']['children'];
        $child = $this->getLudoJsFor('firstname', $children);
        // then
        $this->assertEquals('John', $child['value']);
    }

    /**
     * @test
     */
    public function shouldAddValidationProperties(){
        // given
        $handler = new LudoDBRequestHandler();
        // when
        $form = $handler->handle("LudoJS/TLudoJSPerson/1/form");
        $form = json_decode($form, true);

        $children = $form['response']['children'];
        $child = $this->getLudoJsFor('firstname', $children);
        // then
        $this->assertTrue($child['required']);
    }

    /**
     * @test
     */
    public function shouldReturnFormConfig(){
        // given
        $handler = new LudoDBRequestHandler();
        // when
        $form = $handler->handle("LudoJS/TLudoJSPerson/1/form");
        $form = json_decode($form, true);

        $formConfig = $form['response']['form'];

        $this->assertEquals('TLudoJSPerson', $formConfig['resource']);
    }

    private function getLudoJsFor($column, $children){
        foreach($children as $child){
            if($child['name'] === $column)return $child;
        }
        return null;
    }

    private function getIndexOf($column, $children){
        for($i=0,$count = count($children);$i<$count;$i++){
            if($children[$i]['name'] === $column)return $i;
        }
        return null;
    }
}
