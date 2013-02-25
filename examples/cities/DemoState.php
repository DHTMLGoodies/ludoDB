<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:35
 */
class DemoState extends LudoDBModel
{
    protected $config = array(
        "table" => "demo_state",
        "sql" => "select * from demo_state where id=?",
        "columns" => array(
            "id" => "int auto_increment not null primary key",
            "name" => array(
                "db" => "varchar(255)",
                "access" => "rw"
            ),
            "country" => array(
                "db" => "int",
                "references" => "demo_country(id) on delete cascade",
                "access" => "rw"
            )
        ),
        "static" => array(
            "type" => "state"
        ),
        "data" => array(
            array("name" => "Rogaland", "country" => 1),
            array("name" => "Hordaland", "country" => 1),
            array("name" => "Texas", "country" => 2),
            array("name" => "California", "country" => 2),
            array("name" => "Bavaria", "country" => 3),
        ),
        "indexes" => array("country")

    );
}
