<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13
 * Time: 12:36
 * To change this template use File | Settings | File Templates.
 */
class LudoDBJSONCache extends LudoDBModel
{
    protected $config = array(
        'table' => 'JSON_cache',
        'sql' => 'select * from JSON_cache where key=?',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'key' => 'varchar(512) unique',
            'JSON' => 'mediumtext'
        ),
        'indexes' => array('key')
    );
}
