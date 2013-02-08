<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xait0020
 * Date: 08.02.13
 * Time: 21:59
 */

require_once __DIR__."/../autoload.php";
class LudoDBTreeCollectionTest extends TestBase
{

    /**
     * @test
     */
    public function shouldBeAbleToGetChildren(){
        // given
        $node = new TestNodes();

        // when
        $values = $node->getValues();

        // then
        $this->assertEquals(2,count($values));

    }
}
