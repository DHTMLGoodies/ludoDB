<?php
/**
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:28
 */
class CarCollection extends LudoDBCollection
{
    protected $config = array(
        'model' => 'Car',
        'idField' => 'id',
        'table' => 'car',
        'columns' => array('brand','model'),
        'constructBy' => 'brand'
    );
}
