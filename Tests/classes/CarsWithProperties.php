<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:03
 */
class CarsWithProperties extends LudoDBCollection implements LudoDBService
{
    protected $config = array(
        "sql"=>"select * from car",
        "model"=> "car",
        "childKey" => "properties",
        "hideForeignKeys" => true,
        "merge" => array(
            array(
                "class" =>"AllCarProperties",
                "fk" => "car_id",
                "pk" => "id"
            )
        )
    );

    public function shouldCache($service){
        return false;
    }
    public function validateArguments($service, $arguments){
        return count($arguments) === 0;
    }

    public function validateServiceData($service, $data){
        return true;
    }

    public function getValidServices(){
        return array("read");
    }
}
