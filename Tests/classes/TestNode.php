<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xait0020
 * Date: 08.02.13
 * Time: 22:04
 */
class TestNode extends LudoDBModel
{
    protected $config = array(
        "table" => "test_node",
        "sql" => "select * from test_node where id=?",
        "columns" => array(
            "id" => "int auto_increment not null primary key",
            "title" => array(
                "db" => "varchar(32)",
                "access" => "rw"
            ),
            "parent" => array(
                "db" => "int",
                "references" => "test_node(id)"
            )
        ),
        "data" => array(
            array("title" => "Node 1"),
            array("title" => "Node 2"),
            array("title" => "Node 1.1", "parent" => 1),
            array("title" => "Node 1.2", "parent" => 1),
            array("title" => "Node 1.3", "parent" => 1),
            array("title" => "Node 1.1.1", "parent" => 3),
            array("title" => "Node 1.1.2", "parent" => 3),
            array("title" => "Node 1.1.2.1", "parent" => 5),
            array("title" => "Node 1.1.2.2", "parent" => 5),
            array("title" => "Node 1.1.2.3", "parent" => 5),
            array("title" => "Node 1.1.2.4", "parent" => 5),
            array("title" => "Node 1.1.2.5", "parent" => 5),
            array("title" => "Node 1.1.2.5", "parent" => 5),
            array("title" => "Node 1.1.2.6", "parent" => 5),
            array("title" => "Node 1.1.2.7", "parent" => 5),
            array("title" => "Node 1.1.2.8", "parent" => 5),
            array("title" => "Node 1.1.2.9", "parent" => 5),
            array("title" => "Node 1.1.2.10", "parent" => 5),
            array("title" => "Node 1.1.2.11", "parent" => 5),
            array("title" => "Node 1.1.2.12", "parent" => 5),
            array("title" => "Node 1.1.2.13", "parent" => 5),
            array("title" => "Node 1.1.2.14", "parent" => 5),
            array("title" => "Node 1.1.2.15", "parent" => 5),
            array("title" => "Node 1.1.2.16", "parent" => 5),
            array("title" => "Node 1.1.2.17", "parent" => 5),
            array("title" => "Node 1.1.2.18", "parent" => 5),
            array("title" => "Node 1.1.2.19", "parent" => 5),
            array("title" => "Node 2.1", "parent" => 2),
            array("title" => "Node 2.2", "parent" => 2),
            array("title" => "Node 2.3", "parent" => 2),
            array("title" => "Node 2.4", "parent" => 2),
            array("title" => "Node 2.5", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 15),
            array("title" => "Sub mode", "parent" => 14),
            array("title" => "Sub mode", "parent" => 12),
            array("title" => "Sub mode", "parent" => 12),
            array("title" => "Sub mode", "parent" => 12),
            array("title" => "Sub mode", "parent" => 32),
            array("title" => "Sub mode", "parent" => 4),
            array("title" => "Sub mode", "parent" => 7),
            array("title" => "Sub mode", "parent" => 8),
            array("title" => "Sub mode", "parent" => 8),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 29),
            array("title" => "Sub mode", "parent" => 29),
            array("title" => "Sub mode", "parent" => 29),
            array("title" => "Sub mode", "parent" => 29),
            array("title" => "Sub mode", "parent" => 29),
            array("title" => "Sub mode", "parent" => 31),
            array("title" => "Sub mode", "parent" => 49),
            array("title" => "Scandinavia", "parent" => 56),
            array("title" => "Norway", "parent" => 57),
            array("title" => "Sweden", "parent" => 57),
            array("title" => "Denmark", "parent" => 57),

        )

    );
}
