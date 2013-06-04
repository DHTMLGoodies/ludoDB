<?php
/**
 * Revision
 * User: Alf Magne Kalleland
 * Date: 04.06.13
 * Time: 01:26
 */
class LudoDBRevision extends LudoDBModel
{
    protected $config = array(
        'idField' => 'id',
        'table' => 'LudoDBRevision',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'model' => array(
                'db' => 'varchar(255)',
                'access' => 'rw'
            ),
            'arguments' => array(
                'db' => 'varchar(4000)',
                'access' => 'rw'
            ),
            'data' => array(
                'db' => 'mediumtext',
                'access' => 'rw'
            ),
            'created' => array(
                'db' => 'datetime',
                'access' => 'r'
            )
        ),
        'indexes' => array('model','arguments')
    );
}
