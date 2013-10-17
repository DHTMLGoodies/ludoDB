<?php
/**
 * LudoDB Request Handler - Handle WebService requests
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Request handler class for Front End Controller. This class will handle requests sent
 * by Views and pass them to the correct LudoDBObject's.
 * User: Alf Magne Kalleland
 * Date: 13.01.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */

class LudoDBRequestHandler
{
    /**
     * Resource handled
     * @var LudoDBObject|LudoDBService
     */
    protected $resource;
    /**
     * Service name
     * @var string
     */
    protected $serviceName;
    /**
     * Cache instance
     * @var LudoDBCache
     */
    protected $cacheInstance;
    /**
     * Internal array of valid services for given resource.
     * @var array
     */
    private $validServices = array();
    /**
     * Success value for handles request.
     * @var bool
     */
    private $success = true;
    /**
     * Response message for handled request
     * @var string
     */
    private $message = "";
    /**
     * Response code for handled request
     * @var int
     */
    private $code = 200;
    /**
     * Arguments for given request
     * @var array
     */
    private $arguments;
    /**
     * Key used in response from service handler.
     * @var string
     */
    private $responseKey = 'response';


    /**
     * @var LudoDBAuthenticator
     */
    private $authenticator;

    private $resourceName;

    private $request;

    /**
     * Handle request
     *
     * router.template.php can be used as a template on how to create a controller for a request handler.
     *
     * Example code:
     *
     * <code>
     * require_once(dirname(__FILE__)."/autoload.php");
     * if(file_exists("connection.php")){
     *     require("connection.php");
     * }
     *
     * LudoDBRegistry::set('DEVELOP_MODE', true);
     * LudoDB::enableLogging();
     *
     * $request = isset($_GET['request']) ? $_GET['request'] : $_POST['request'];
     * $data = isset($_POST['data']) ? $_POST['data'] : null;
     *
     * $handler = new LudoDBRequestHandler();
     * echo $handler->handle($request, $data);
     * </code>
     *
     * @param String $request
     * @param Array|Null $data
     * @return string
     * @throws LudoDBObjectNotFoundException
     * @throws LudoDBServiceNotImplementedException
     * @throws LudoDBInvalidServiceException
     * @throws LudoDBInvalidArgumentsException
     */
    public function handle($request, $data = null)
    {
        $this->request = $request;

        try {

            $data = $this->runAndRemoveProgressBar($data);

            if(!isset($this->request)){
                throw new LudoDBInvalidArgumentsException("No request");
            }

            $this->arguments = $this->getArguments();
            $this->resourceName = $this->getClassName($request);
            $this->resource = $this->getResource($this->arguments);
            $this->validServices = $this->getValidServices();
            $this->serviceName = $this->getServiceName();

            if (!in_array($this->serviceName, $this->validServices)) {
                throw new LudoDBInvalidServiceException('Invalid service ' . $this->serviceName . ', resource: ' . $this->getClassName($request));
            }

            if (!$this->resource->validateArguments($this->serviceName, $this->arguments)) {
                throw new LudoDBInvalidArgumentsException('Invalid constructor arguments for resource:' . $this->getClassName($request) . ', service:' . $this->serviceName . ", arguments: " . implode(",", $this->arguments));
            }

            if (!$this->resource->validateServiceData($this->serviceName, $data)) {
                throw new LudoDBInvalidArgumentsException('Invalid service data/arguments for resource:' . $this->getClassName($request) . ', service:' . $this->serviceName . ", arguments: " . implode(",", $this->arguments));
            }

            if ($this->serviceName === 'delete' || $this->serviceName === 'read') {
                if ($this->resource instanceof LudoDBModel && !$this->resource->getId()) {
                    throw new LudoDBObjectNotFoundException('Object not found');
                }
            }

            if(isset($this->authenticator)){
                $success = $this->authenticator->authenticate($this->resourceName, $this->serviceName, $this->arguments, $data);
                if(!$success){
                    throw new LudoDBUnauthorizedException('Not authorized');
                }
            }

            if (!method_exists($this->resource, $this->serviceName)) {
                throw new LudoDBServiceNotImplementedException("Service " . $this->serviceName . " not implemented");
            }

            if($this->resource->shouldCache($this->serviceName)){
                return $this->toJSON($this->getCached($data));
            }else{
                return $this->toJSON($this->resource->{$this->serviceName}($data));
            }


        } catch (Exception $e) {
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            $this->success = false;
            return $this->toJSON(array());
        }
    }

    private function runAndRemoveProgressBar($data){

        if(!empty($data) && is_array($data) && isset($data['LudoDBProgressID'])){
            $pr = LudoDBProgress::getInstance();
            $pr->save($data['LudoDBProgressID']);
            unset($data['LudoDBProgressID']);

        }
        return $data;
    }

    /**
     * Set authenticator, a class implementing the LudoDBAuthenticator
     * interface. When set, the authenticate method of this class will be
     * called on request. If exception
     * @param LudoDBAuthenticator $authenticator
     */
    public function setAuthenticator(LudoDBAuthenticator $authenticator){
        $this->authenticator = $authenticator;
    }

    /**
     * Return data from handler in JSON format.
     * @param array $data
     * @return string
     */
    private function toJSON($data = array())
    {
        if($this->success){
            $this->message = $this->resource->getOnSuccessMessageFor($this->serviceName);
        }
        $ret = array(
            'success' => $this->success,
            'message' => $this->message,
            'code' => $this->code,
            'resource' => get_class($this->resource),
            'service' => $this->serviceName,
            $this->responseKey => $data
        );
        if (LudoDB::isLoggingEnabled()) {
            $ret['log'] = array(
                'time' => LudoDB::getElapsed(),
                'queries' => LudoDB::getQueryCount()
            );
        }
        return json_encode($ret);
    }

    /**
     * Get arguments from request sent to handler.
     * @param array $request
     * @return array
     */
    protected function getArguments()
    {
        $ret = explode("/", $this->request);
        array_shift($ret);
        array_pop($ret);
        return $ret;
    }

    /**
     * Get name of resource for request.
     * @param array $request
     * @param array $args
     * @return null|object
     * @throws LudoDBClassNotFoundException
     */
    protected function getResource($args = array())
    {
        if (isset($this->resourceName)) {
            $cl = $this->getReflectionClass($this->resourceName);
            if (empty($args)) {
                return $cl->newInstance();
            } else {
                return $cl->newInstanceArgs($args);
            }
        }
        throw new LudoDBClassNotFoundException('Invalid request for: ' . $this->request);
    }

    /**
     * Return valid services for handled resource.
     * @param array $request
     * @return array|mixed
     */
    private function getValidServices()
    {
        if (isset($this->resourceName)) {
            $servicesMethod = $this->getReflectionClass($this->resourceName)->getMethod('getValidServices');
            return isset($servicesMethod) ? $servicesMethod->invoke($this->resource) : array();
        }
        return array();
    }

    /**
     * Use Reflection to get instance of resource class
     * @param $className
     * @return ReflectionClass
     * @throws LudoDBClassNotFoundException
     */
    private function getReflectionClass($className)
    {
        $cl = new ReflectionClass($className);
        if (!$cl->implementsInterface('LudoDBService')) {
            throw new LudoDBClassNotFoundException($className . " is not an instance of LudoDBService");
        }
        return $cl;
    }

    /**
     * Return name of resource/class to be handled.
     * @param $request
     * @return string|null
     */
    private function getClassName($request)
    {
        $tokens = explode("/", $request);
        return class_exists($tokens[0]) ? $tokens[0] : null;
    }

    /**
     * Return service method to execute
     * @param $request
     * @return mixed
     */
    protected function getServiceName()
    {
        return array_pop(explode("/", $this->request));
    }

    /**
     * Return data from cache
     * @param array $requestData
     * @return array
     */
    private function getCached($requestData = array())
    {
        if (empty($requestData)) $requestData = null;
        if ($this->ludoDBCache()->hasData()) {
            $data = $this->ludoDBCache()->getCache();
        }else {
            $data = $this->resource->{$this->serviceName}($requestData);
            $this->ludoDBCache()->setCache($data)->commit();
        }
        return $data;
    }

    /**
     * Return LudoDBCache instance
     * @return LudoDBCache
     */
    protected function ludoDBCache()
    {
        if (!isset($this->cacheInstance)) {
            $this->cacheInstance = new LudoDBCache($this->resource, $this->arguments);
        }
        return $this->cacheInstance;
    }

    /**
     * Clear cache instance
     */
    public function clearCacheObject()
    {
        $this->cacheInstance = null;
    }
}
