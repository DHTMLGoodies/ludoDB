<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13


 */
class TestGame extends LudoDBModel
{
    protected $JSONConfig = true;

    public function getDatabaseId(){
        return $this->getValue('databaseId');
    }
}
