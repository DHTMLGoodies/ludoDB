<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:35
 */
class DemoCountry extends LudoDBModel
{
    protected $config = array(
        "table" => "demo_country",
        "sql" => "select * from demo_country where id=?",
        "columns" => array(
            "id" => "int auto_increment not null primary key",
            "name" => array(
                "db" => "varchar(255)",
                "access" => "rw"
            )
        ),
        "static"=>array(
            "type" => "country"
        ),
        "data" => array(
            array("name" => "Norway"),
            array("name" => "United States"),
            array("name" => "Germany"),
        )
    );
}
