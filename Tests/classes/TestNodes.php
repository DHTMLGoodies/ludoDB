<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xait0020
 * Date: 08.02.13
 * Time: 22:13
 */
class TestNodes extends LudoDBTreeCollection implements LudoDBService
{
    protected $config = array(
        "sql" => "select * from test_node order by parent,id",
        "childKey" => "children",
        "fk" => "parent",
        "pk" => "id"
    );

    public function validateService($service, $arguments){
        return count($arguments) === 0;
    }

    public static function getValidServices(){
        return array('read');
    }
}
