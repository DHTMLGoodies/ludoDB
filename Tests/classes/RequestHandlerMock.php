<?php
/**
 * Mock class where private methods are public
 * User: Alf Magne
 * Date: 30.01.13
 * Time: 13:15
 * To change this template use File | Settings | File Templates.
 */
class RequestHandlerMock extends LudoDBRequestHandler
{

    public $model;
    public $action;

    public function getModel(array $request, $args = array()){
        return parent::getModel($request, $args);
    }

    public function getAction($request){
        return parent::getAction($request);
    }

    public function getArguments($request){
        return parent::getArguments($request);
    }
}
