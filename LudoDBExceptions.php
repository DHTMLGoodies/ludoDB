<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 30.01.13


 */

class LudoDBException extends Exception{
    protected $code = 400;
}

class LudoDBClassNotFoundException extends LudoDBException
{
    protected $code = 404;
}

class LudoDBObjectNotFoundException extends LudoDBException{
    protected $code = 404;
}

class LudoDBConnectionException extends LudoDBException{

}

class LudoDBInvalidArgumentsException extends LudoDBException{

}

class LudoDBUnauthorizedException extends LudoDBException{
    protected $code = 401;
}

class LudoDBServiceNotImplementedException extends LudoDBException{
    protected $code = 404;
}


class LudoDBInvalidServiceException extends Exception{
    protected $code = 405;
}

class LudoDBInvalidConfigException extends LudoDBException{

}