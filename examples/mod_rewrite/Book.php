<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xait0020
 * Date: 03.02.13
 * Time: 13:23
 */
class Book extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true;
    public static $validServices = array('read','save');
}
