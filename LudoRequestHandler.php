<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne Kalleland
 * Date: 13.01.13
 * Time: 16:24
 */
class LudoRequestHandler
{
    public function __construct($request){
        $this->handle($request);
    }

    private function handle($request){
        $cl = $this->getClassForHandler($request);
        if(isset($cl)){
            $cl->setValues($request);
            $cl->commit();
        }
    }

    /**
     * @param $request
     * @return LudoDBObject|null
     */

    private function getClassForHandler($request){
        $className = $this->getClassName($request);
        if(isset($className))return new $className;
        return null;
    }

    /**
     * @param $request
     * @return string|null
     */
    private function getClassName($request){
        if(isset($request['model'])) return $request['model'];
        return isset($request['form']) ? $request['form'] : null;
    }

    public function __toString(){
        return "";
    }
}
