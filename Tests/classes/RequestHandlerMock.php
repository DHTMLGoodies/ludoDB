<?php
/**
 * Mock class where private methods are public
 * User: Alf Magne
 * Date: 30.01.13
 * Time: 13:15
 * To change this template use File | Settings | File Templates.
 */
class RequestHandlerMock extends LudoRequestHandler
{
    public function __call($name, $arguments){
        if(method_exists($this, $name)){
            $this->$name($arguments[0]);
        }
    }

    public function getCRUDAction(){
        return parent::getCRUDAction();
    }
}
