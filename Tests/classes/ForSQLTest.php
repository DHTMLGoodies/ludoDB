<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13
 * Time: 11:26
 * To change this template use File | Settings | File Templates.
 */
class ForSQLTest extends LudoDBModel
{

    public function setConfig($config){
        $this->config = $config;
    }

    public function setConstructorValues($values){
        $this->arguments = $values;
        $this->parser = $this->getConfigParserInstance();
    }
}
