<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 23.12.12
 * Time: 02:30
 */
$res = mysql_connect("localhost", 'root', 'administrator');
mysql_select_db('PHPUnit', $res);

require_once(__DIR__ . "/autoload.php");

$city = new City();
$city->drop();
$city->createTable();
$city->setZip('4330');
$city->setCity('Ålgård');
$city->commit();

$person = new Person();
$person->drop();
$person->createTable();

$person->setLastName('Kalleland');
$person->setFirstname('Alf Magne');
$person->setAddress('Rundaberget 27');
$person->setZip('4330');
$person->commit();

$phone = new Phone();
$phone->drop();
$phone->createTable();


$numbers = array('41647781', '51415989');
foreach ($numbers as $number) {
    $phone = new Phone();
    $phone->setUserId($person->getId());
    $phone->setPhone($number);
    $phone->commit();
}

$person = new Person($person->getId());
echo $person->getJSON();