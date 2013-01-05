<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 23.12.12
 * Time: 21:04
 */
class DreamTable extends LudoDbTable
{
    protected $config = array(
        'table' => 'DreamTable',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'firstname' => 'varchar(64)',
            'lastname' => 'varchar(64)',
            'zip' => 'varchar(15)',
            'city' => array(
                'join' => 'City',
                'get' => 'getCity',
                'set' => 'setCity'
            ),
            'phone' => array(
                'class' => 'PhoneCollection'
            )
        ),
        'joins' => array(
            'class' => 'City',
            'fk' => 'zip',
            'pk' => 'zip'
        )
    );
}
