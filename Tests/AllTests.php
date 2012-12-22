<?php

require_once(__DIR__."/../autoload.php");

class AllTests
{

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->setName('AllTests');
        $suite->addTestSuite("DBTest");
        $suite->addTestSuite("CollectionTest");
        $suite->addTestSuite("SQLTest");
        $suite->addTestSuite("JSONTest");
        return $suite;
    }
}