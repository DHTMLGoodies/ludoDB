<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xait0020
 * Date: 03.02.13

 */
class Author extends LudoDBModel
{
    protected $JSONConfig = true;

    public function setName($name){
        $this->setValue('name', $name);
    }
}
