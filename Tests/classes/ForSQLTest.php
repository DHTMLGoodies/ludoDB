<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 10.01.13


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
