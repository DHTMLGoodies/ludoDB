<?php

require_once(__DIR__ . "/../autoload.php");

class FinderTest extends TestBase
{
    public function setUp(){
        parent::setUp();
    }

    /**
     * @test
     */
    public function shouldBeAbleToInstantiateByFinders(){
        // given
        $finder = new LudoFinder(new Car());

        // when
        $car = $finder->where('brand', 'Opel')->find();

        // then
        $this->assertEquals('Opel', $car->getBrand());
    }

    /**
     * @test
     */
    public function shouldBeAbleToDefineMultipleSearchFields(){
        // given
        $finder = new LudoFinder(new Car());

        // when
        $car = $finder->where('brand', 'Audi')->where('model', 'a4')->find();

        // then
        $this->assertEquals('Audi', $car->getBrand());
    }
}
