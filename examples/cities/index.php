<?php
/**
 * Demo of nested collections. DemoCountries merges DemoStates
 * and DemoStates merges DemoCities.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 16:34
 */

require_once(__DIR__ . "/autoload.php");

ini_set('display_errors', 'on');
date_default_timezone_set("Europe/Berlin");

LudoDB::setDb("PHPUnit");
LudoDB::setUser("root");
LudoDB::setPassword("administrator");
LudoDB::setHost("127.0.0.1");

$c = new DemoCountry();
if (!$c->exists()) {
    $util = new LudoDBUtility();
    $util->dropAndCreate(array("DemoState", "DemoCity", "DemoCountry"));
}


LudoDB::enableLogging(); // get number of queries and server time in response

$handler = new LudoDBRequestHandler();
echo $handler->handle("DemoCountries/read");
