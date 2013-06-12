<?php
/**
 * Mock class where private methods are public
 * User: Alf Magne
 * Date: 30.01.13


 */
class RequestHandlerMock extends LudoDBRequestHandler
{

    public $model;
    public $action;

    public function getResource($request, $args = array()){

        return parent::getResource($request, $args);
    }

    public function getServiceName(){
        return parent::getServiceName();
    }

    public function getArguments(){
        return parent::getArguments();
    }
}
