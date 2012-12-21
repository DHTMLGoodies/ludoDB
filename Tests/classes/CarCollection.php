<?php
/**
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * Time: 21:28
 */
class CarCollection extends LudoDbCollection
{
    protected $tableName = 'Car';
    protected $config = array(
        'columns' => array('brand','model'),
        'lookupField' => 'brand'
    );
}
