<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:44
 */
class DemoStates extends LudoDBCollection
{
    protected $config = array(
        "sql" => "select * from demo_state order by name",
        "model" => "DemoState",
        "hideForeignKeys" => true,
        "merge" => array(
            array(
                "childKey" => "children",
                "class" => "DemoCities",
                "fk" => "state",
                "pk" => "id"
            )
        )
    );
}
