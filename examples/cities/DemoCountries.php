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
        "childKey" => "children",
        "model" => "DemoCountry",
        "merge" => array(
            array(
                "class" => "DemoStates",
                "hideForeignKeys" => true,
                "fk" => "country",
                "pk" => "id"
            )
        )
    );

    public function getValidServices(){
        return array("read");
    }

    public function validateArguments($service, $arguments){
        return count($arguments) === 0;
    }

    public function validateServiceData($service, $data){
        return empty($data);
    }

    public function cacheEnabledFor($service){
        return true;
    }

    public function getOnSuccessMessageFor($service){
        return "";
    }
}
