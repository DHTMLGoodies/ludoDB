<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 30.01.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */

class LudoDBException extends Exception{
    protected $code = 400;
}

/**
 * Class not found exception.
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBClassNotFoundException extends LudoDBException
{
    protected $code = 404;
}

/**
 * Object not found exception.
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBObjectNotFoundException extends LudoDBException{
    protected $code = 404;
}

/**
 * DB Connection error exception.
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBConnectionException extends LudoDBException{

}

/**
 * Invalid constructor arguments exception.
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBInvalidArgumentsException extends LudoDBException{

}
/**
 * Unauthorized exception
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBUnauthorizedException extends LudoDBException{
    protected $code = 401;
}

/**
 * Service not implemented exception. Executed when a service is returned from getValidServices, but
 * the service method is not implemented.
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBServiceNotImplementedException extends LudoDBException{
    protected $code = 404;
}


/**
 * Invalid service exception. Executed on call for service name not returned by getValidServices.
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBInvalidServiceException extends Exception{
    protected $code = 405;
}

/**
 * Invalid Config Exception. Executed on invalid configuration of LudoDB classes
 * Thrown by LudoDBRequestHandler
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBInvalidConfigException extends LudoDBException{

}