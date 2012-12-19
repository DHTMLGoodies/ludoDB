<?php
/**
 * Class for collections
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:24
 */
require_once(__DIR__."/../autoload.php");

class CollectionTest extends TestBase
{
    /**
     * @text
     */
    public function shouldBeAbleToGetCollection(){
        // given
        $cars = new CarCollection();
        $cars->by('model', 'Opel')->find();
    }
}
