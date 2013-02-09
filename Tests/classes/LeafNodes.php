<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 14:28
 */
class LeafNodes extends LudoDBCollection
{
    protected $config = array(
        "sql" => "select * from leaf_node order by id",
        "model" => "LeafNode"
    );
}
