<?php
/**
 *
 * User: Alf Magne
 * Date: 12.06.13
 * Time: 13:33
 */
class AccessDeniedAuthenticator implements LudoDBAuthenticator
{
    public function authenticate($service, $arguments, $data){
        return false;
    }
}
