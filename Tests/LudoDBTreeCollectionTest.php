<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xait0020
 * Date: 08.02.13
 * Time: 21:59
 */

require_once __DIR__."/../autoload.php";

error_reporting(E_ALL);
ini_set('display_errors','on');
class LudoDBTreeCollectionTest extends TestBase
{

    /**
     * @test
     */
    public function shouldBeAbleToGetTopNodes(){
        // given
        $node = new TestNodes();

        // when
        $values = $node->getValues();

        // then
        $this->assertEquals(3,count($values));

    }
    /**
     * @test
     */
    public function shouldBeAbleToGetChildNodes(){
        // given
        $node = new TestNodes();

        // when
        $values = $node->getValues();

        $children = $values[0]["children"];

        // then
        $this->assertEquals(3,count($children));

    }
    /**
     * @test
     */
    public function shouldBeAbleToGetGrandChildren(){
        // given
        $node = new TestNodes();

        // when
        $values = $node->getValues();

        $children = $values[0]["children"][0]['children'];

        // then
        $this->assertEquals(4,count($children));
    }

    /**
     * @test
     */
    public function shouldGetParserKeys(){
        // given
        $node = new TestNodes();

        // when
        $this->assertEquals('parent', $node->configParser()->getFK());
        $this->assertEquals('id', $node->configParser()->getPK());
        $this->assertEquals('children', $node->configParser()->getChildKey());
    }

    /**
     * @test
     */
    public function shouldBeAbleToMergeInCollections(){
        // given
        $nodes = new TestNodesWithLeafs();

        // when
        $values = $nodes->getValues();
        $rootNode = $values[2];

        // then
        $this->assertEquals(1, count($nodes->configParser()->getMerged()));
        $this->assertEquals(3, count($rootNode['children']));

    }

    /**
     * @test
     */
    public function shouldBeAbleToUseLimitQuery(){
        // given
        LudoDB::enableSqlLogging();
        $this->createPeople();
        $people = new People(4330);
        $values = $people->getValues(0, 10);

        // then
        $this->assertEquals(10, count($values));

    }

    public function shouldBeAbleToSpecifyLimitInSql(){
        // given
        LudoDB::enableSqlLogging();
        $this->createPeople();
        $people = new PeoplePaged(0, 10);
        $values = $people->getValues();

        // then
        $this->assertEquals(10, count($values));
    }

    private function createPeople(){
        for($i=0;$i<50;$i++){
            $person = new Person();
            $person->setFirstname('John');
            $person->setLastname('Wayne');
            $person->setZip(4330);
            $person->setAddress('Somewhere');
            $person->commit();
        }
    }
}
