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
                "access" => "rw",
                "references" => "test_node(id)"
            )
        ),
        "data" => array(
            array("title" => "Node 1"),
            array("title" => "Node 2"),
            array("title" => "Root node"),
            array("title" => "Node 1.1", "parent" => 1),
            array("title" => "Node 1.2", "parent" => 1),
            array("title" => "Node 1.3", "parent" => 1),
            array("title" => "Node 1.1.1", "parent" => 4),
            array("title" => "Node 1.1.2", "parent" => 4),
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
            array("title" => "Sub mode", "parent" => 8),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 2),
            array("title" => "Sub mode", "parent" => 4),
            array("title" => "Scandinavia", "parent" => 4),
            array("title" => "Norway", "parent" => 5),
            array("title" => "Sweden", "parent" => 5),
            array("title" => "Denmark", "parent" => 5),


        )

    );
}
