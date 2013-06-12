<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 13.04.13
 * Time: 19:20
 */
class TLudoJSCountries extends LudoDBCollection implements LudoDBService
{
    protected $config = array(
        "sql" => "select * from LudoJSCountry order by name"
    );

    public function getValidServices(){
        return array('read');
    }

    public function validateArguments($service, $arguments){
        return empty($arguments);
    }

    public function validateServiceData($service, $data){
        return empty($data);
    }
}
