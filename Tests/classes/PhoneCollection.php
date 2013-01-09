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
        'idField' => 'id',
        'table' => 'Phone',
        'queryFields' => 'user_id',
        'columns' => array(
            'phone'
        ),
        'orderBy' => 'id'
    );

    public function getValues(){
        $ret = array();
        foreach($this as $value){
            $ret[] = $value['phone'];
        }
        return $ret;
    }
}
