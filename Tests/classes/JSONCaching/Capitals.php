<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 28.01.13
 * Time: 15:16
 * To change this template use File | Settings | File Templates.
 */
class Capitals extends LudoDBCollection
{
    protected $JSONConfig = true;
    protected $JSONCaching = true;

    public function __construct($fromZip, $toZip){
        parent::__construct($fromZip, $toZip);
    }
}