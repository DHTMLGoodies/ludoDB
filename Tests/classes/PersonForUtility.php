<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 24.04.13
 * Time: 00:59
 */
class PersonForUtility extends LudoDBModel
{
    protected $config = array(
        'table' => 'PersonForUtility',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            "firstname" => array(
                "db" => "varchar(64)"
            ),
            "zip" => array(
                "db" => "int"
            )
        )

    );
}
