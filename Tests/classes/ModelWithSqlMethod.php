<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 06.06.13
 * Time: 21:03
 */
class ModelWithSqlMethod extends LudoDBModel
{

    protected $config = array(
        'table' => 'Person'
    );

    public function getSql(){
        return is_numeric($this->arguments[0]) ? "select * from person where id=?" : "select * from person where firstname=?";
    }

    public function sqlHandler(){
        return parent::sqlHandler();
    }
}
