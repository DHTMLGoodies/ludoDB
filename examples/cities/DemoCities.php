<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:44
 */
class DemoCities extends LudoDBCollection
{
    protected $config = array(
        "sql" => "select * from demo_city order by name",
        "model" => "DemoCity"
    );
}
