<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 20.12.12
 * Time: 13:48
 */
class Phone extends LudoDbTable
{
    protected $config = array(
        'table' => 'Phone',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'phone' => 'varchar(32)',
            'user_id' => 'int'
        )
    );

    public function setPhone($number){
        $this->setValue('phone', $number);
    }

    public function getPhone(){
        return $this->getValue('phone');
    }

    public function setUserId($id){
        $this->setValue('user_id', $id);
    }

    public function getUserId(){
        return $this->getValue('user_id');
    }
}
