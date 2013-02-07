<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13


 */
class PersonForConfigParser extends LudoDBModel
{
    protected $config = array(
        'table' => 'Person',
        'idField' => 'id',
        'constructBy' => 'id',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'firstname' => 'varchar(32)',
            'lastname' => 'varchar(32)',
            'area_code' => array(
                'db' => 'varchar(16)',
                'access' => 'r'
            ),
            'address' => array(
                'db' => 'varchar(64)',
                'access' => 'w'
            ),
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
