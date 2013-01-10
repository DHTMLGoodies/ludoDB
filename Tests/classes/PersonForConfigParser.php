<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13
 * Time: 12:15
 * To change this template use File | Settings | File Templates.
 */
class PersonForConfigParser extends LudoDbTable
{
    protected $config = array(
        'table' => 'Person',
        'idField' => 'id',
        'constructorParams' => 'id',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'firstname' => 'varchar(32)',
            'lastname' => 'varchar(32)',
            'address' => 'varchar(64)',
            'zip' => 'varchar(5)',
            'phone' => array(
                'class' => 'PhoneCollection'
            ),
            'city' => array(
                'class' => 'City',
                'get' => 'getCity',
                'set' => 'setCity'
            )
        ),
        'classes' => array(
            'city' => array(
                'fk' => 'zip'
            )
        )
    );
}
