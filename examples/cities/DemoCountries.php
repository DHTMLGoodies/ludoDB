<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:45
 */
class DemoCountries extends LudoDBCollection implements LudoDBService
{
    protected $config = array(
        "sql" => "select * from demo_country order by name",
        "childKey" => "states/counties",
        "merge" => array(
            array(
                "class" => "DemoStates",
                "fk" => "country",
                "pk" => "id"
            )
        )
    );

    public static function getValidServices(){
        return array("read");
    }

    public function validateService($service, $arguments){
        return count($arguments) === 0;
    }

    public function cacheEnabled(){
        return false;
    }
}
