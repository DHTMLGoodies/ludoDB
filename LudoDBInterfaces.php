<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 31.01.13
 * Time: 19:07
 * To change this template use File | Settings | File Templates.
 */
interface LudoDBAdapter
{

    public function connect();

    public function query($sql, $params = array());

    public function one($sql, $params = array());

    public function countRows($sql, $params = array());

    public function getInsertId();

    public function nextRow($result);

    public function getValue($sql, $params = array());

    public function escapeString($string);
}
