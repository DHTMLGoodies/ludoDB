<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:35
 */
class DemoCity extends LudoDBModel
{
    protected $config = array(
        "table" => "demo_city",
        "sql" => "select * from demo_city where id=?",
        "columns" => array(
            "id" => "int auto_increment not null primary key",
            "name" => array(
                "db" => "varchar(255)",
                "access" => "rw"
            ),
            "state" => array(
                "db" => "int",
                "references" => "demo_state(id) on delete cascade",
                "access" => "rw"
            )
        ),
        "static" => array(
            "type" => "city"
        ),
        "data" => array(
            array("name" => "Stavanger", "state"=> 1),
            array("name" => "Sandnes", "state"=> 1),
            array("name" => "Haugesund", "state"=> 1),
            array("name" => "Bergen", "state"=> 2),
            array("name" => "Houston", "state"=> 3),
            array("name" => "Austin", "state"=> 3),
            array("name" => "San Fransisco", "state"=> 4),
            array("name" => "Los Angeles", "state"=> 4),
            array("name" => "San Diego", "state"=> 4),
            array("name" => "Munich", "state"=> 5),
        ),
        "indexes" => array("state")

    );
}
