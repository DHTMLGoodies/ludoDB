<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 14:15
 */
class LeafNode extends LudoDBModel
{
    protected $config = array(
        "table" => "leaf_node",
        "columns" => array(
            "id" => "int auto_increment not null primary key",
            "name" => array(
                "db" => "varchar(32)",
                "access" => "rw"
            ),
            "parent_node_id" => array(
                "db" => "int",
                "access" => "rw",
                "references" => "test_node(id) on delete cascade"
            )
        ),
        "static" => array(
            "type" => "leaf"
        ),
        "data" => array(
            array("name" => "Leaf node", "parent_node_id" => 3),
            array("name" => "Leaf node3", "parent_node_id" => 3),
            array("name" => "Leaf node4", "parent_node_id" => 3),
        )

    );
}
