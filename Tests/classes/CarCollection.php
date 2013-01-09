<?php
/**
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:28
 */
class CarCollection extends LudoDbCollection
{
    protected $config = array(
        'idField' => 'id',
        'table' => 'car',
        'columns' => array('brand','model'),
        'queryFields' => 'brand'
    );
}
