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

    public function cacheEnabled(){
        return false;
    }
    public function validateService($service, $arguments){
        return count($arguments) === 0;
    }

    public static function getValidServices(){
        return array("read");
    }
}
