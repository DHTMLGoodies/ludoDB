<?php
/**
 *
 * User: Alf Magne
 * Date: 12.06.13
 * Time: 13:26
 */
class PersonWithAuthInterface extends Person implements LudoDBAuthentication
{
    public function authenticate($service, $arguments, $data){

    }
}
