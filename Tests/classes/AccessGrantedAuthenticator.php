<?php
/**
 *
 * User: Alf Magne
 * Date: 12.06.13
 * Time: 13:33
 */
class AccessGrantedAuthenticator implements LudoDBAuthenticator
{
    public function authenticate($resource, $service, $arguments, $data){
        return true;
    }
}
