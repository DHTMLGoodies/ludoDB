<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 12.01.13
 * Time: 09:21
 */

require_once(__DIR__ . "/../autoload.php");

class ObjectCreatorTest extends TestBase
{
    public function setUp()
    {
        parent::setUp();

        $brand = new Brand();
        $brand->drop()->yesImSure();
        $brand->createTable();

        $brands = array(
            array('name' => 'Toshiba', 'category' => 10, "description" => "Water pump", "price" => 2000),
            array('name' => 'Mitsubishi', 'category' => 4, "description" => "Water pump", "price" => 2300),
            array('name' => 'LG', 'category' => 10, "description" => "Water pump", "price" => 2020),
            array('name' => 'Samsung', 'category' => 5, "description" => "Water pump", "price" => 1900),
            array('name' => 'Toshiba', 'category' => 10, "description" => "Water pump", "price" => 3000),
            array('name' => 'Toshiba 2', 'category' => 11, "description" => "Water pump", "price" => 1400),
            array('name' => 'Toshiba 3', 'category' => 11, "description" => "Water pump", "price" => 1300),
            array('name' => 'Toshiba 4', 'category' => 11, "description" => "Water pump", "price" => 1500),
        );

        foreach ($brands as $b) {
            $brand = new Brand();
            $brand->setBrandName($b['name']);
            $brand->setCategory($b['category']);
            $brand->setDescription($b['description']);
            $brand->setPrice($b['price']);
            $brand->commit();
        }
    }

    /**
     * @test
     */
    public function shouldFindPossibleColumnsToPopulateBy(){
        // given
        $pump = new Brand();

        // then
        $this->assertTrue($pump->configParser()->canBePopulatedBy('id'));
        $this->assertFalse($pump->configParser()->canBePopulatedBy('description'));
        $this->assertTrue($pump->configParser()->canBePopulatedBy('category'));
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateObjectsUsingWhereEquals()
    {
        // given
        $pump = new Brand();
        $pump->where('category')->equals(4)->create();

        // then
        $this->assertEquals(4, $pump->getCategory());
    }

    /**
     * @test
     */
    public function shouldBeAbleToConstructingUsingMultipleWhereEquals(){
        // given
        $pump = new Brand();

        // when
        $pump->where('category')->equals(11)->where('price')->equals(1300)->create();

        // then
        $this->assertEquals(1300, $pump->getPrice());
    }
}
