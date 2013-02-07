<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 05.02.13

 */
class Movie extends LudoDBModel
{
    protected $JSONConfig = true;

    public function getTitle(){
        return $this->getValue('title');
    }
}
