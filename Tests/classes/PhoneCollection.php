<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * Time: 14:12
 */
class PhoneCollection extends LudoDbCollection
{
    protected $config = array(
        'table' => 'Phone',
        'lookupField' => 'user_id',
        'columns' => array(
            'phone'
        ),
        'orderBy' => 'id'
    );

}
