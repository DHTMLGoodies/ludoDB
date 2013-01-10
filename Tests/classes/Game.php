<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13
 * Time: 15:51
 * To change this template use File | Settings | File Templates.
 */
class Game extends LudoDbTable
{
    protected $JSONConfig = true;

    public function getDatabaseId(){
        return $this->getValue('databaseId');
    }
}
