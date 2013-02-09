<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 23.12.12

 */
class AllCarProperties extends LudoDBCollection
{
    protected $config = array(
        'sql' => "select property,propertyValue,car_id from carProperty order by property",
        "model" => "CarProperty"
    );
}
