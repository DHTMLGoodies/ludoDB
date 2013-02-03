<?php
/**
 * Simple model for a book.
 * User: xAlf Magne Kalleland
 * Date: 03.02.13
 * Time: 13:23
 */
class Book extends LudoDBModel implements LudoDBService
{
    protected $JSONConfig = true; // Config on JSONConfig/Book.json
    public static $validServices = array('read','save');
}
