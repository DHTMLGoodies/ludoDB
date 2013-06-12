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

    public function getResource(array $request, $args = array()){

        return parent::getResource($request, $args);
    }

    public function getServiceName($request){
        return parent::getServiceName($request);
    }

    public function getArguments($request){
        return parent::getArguments($request);
    }
}
