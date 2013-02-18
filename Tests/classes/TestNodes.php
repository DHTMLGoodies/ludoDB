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
        "model" => "TestNode",
        "fk" => "parent",
        "pk" => "id",
        "static" => array(
            "type" => "node"
        )
    );

    public function validateArguments($service, $arguments){
        return count($arguments) === 0;
    }

    public function validateServiceData($service, $data){
        return true;
    }

    public function getValidServices(){
        return array('read');
    }

    public function shouldCache($service){
        return true;
    }
}
