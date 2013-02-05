<?php
/**
 * Request handler class for Front End Controller. This class will handle requests sent
 * by Views and pass them to the correct LudoDBObjects.
 * User: Alf Magne Kalleland
 * Date: 13.01.13
 * Time: 16:24
 */
class LudoDBRequestHandler
{
    /**
     * @var LudoDBObject
     */
    protected $model;
    protected $serviceName;
    protected $cacheInstance;
    private $validServices = array();
    private $success = true;
    private $message = "";
    private $code = 200;
    private $arguments;

    public function __construct()
    {

    }

    public function handle($request)
    {
        $request = $this->getParsed($request);
        try {
            $this->validServices = $this->getValidServices($request);
            $this->arguments = $this->getArguments($request);
            $this->model = $this->getModel($request, $this->arguments);
            $this->serviceName = $this->getServiceName($request);

            $this->model->validate($this->serviceName, $request['data']);

            if (!in_array($this->serviceName, $this->validServices)) {
                throw new LudoDBException('Invalid service ' . $this->serviceName, 400);
            }

            if (!$this->model->areValidServiceArguments($this->serviceName, $this->arguments)) {
                throw new LudoDBException('Invalid arguments for ' . $this->serviceName . ", arguments: " . implode(",", $this->arguments), 400);
            }

            if ($this->serviceName === 'delete' || $this->serviceName === 'read') {
                if (!$this->model->getId() && $this->model instanceof LudoDBModel) {
                    throw new LudoDBException('Object not found', 404);
                }
            }

            if (!method_exists($this->model, $this->serviceName)) {
                throw new Exception("Service " . $this->serviceName . " not implemented", 400);
            }

            switch ($this->serviceName) {
                case 'read':
                    return $this->toJSON($this->getValues());
                case 'save':
                    return $this->toJSON($this->model->save($request['data']));
                case 'delete':
                    return $this->toJSON($this->model->delete());
                default:
                    if (method_exists($this->model, $this->serviceName)) {
                        return $this->toJSON($this->model->{$this->serviceName}($request['data']));
                    }
            }
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            $this->success = false;
            return $this->toJSON(array());
        }
        throw new LudoDBException("Invalid request for " . $this->serviceName, 400);
    }

    private function getParsed($request)
    {
        if (is_string($request)) $request = array('request' => $request);
        $lastChar = substr($request['request'], strlen($request['request']) - 1, 1);
        if ($lastChar === '/') {
            $request['request'] = substr($request['request'], 0, strlen($request['request']) - 1);
        }
        if (!isset($request['data'])) $request['data'] = array();
        return $request;
    }

    private function toJSON($data = array())
    {
        $ret = array(
            'success' => $this->success,
            'message' => $this->message,
            'code' => $this->code,
            'response' => $data
        );
        if (LudoDB::isLoggingEnabled()) {
            $ret['log'] = array(
                'time' => LudoDB::getElapsed(),
                'queries' => LudoDB::getQueryCount()
            );
        }
        return json_encode($ret);
    }

    protected function getArguments(array $request)
    {
        $ret = array();
        $tokens = explode("/", $request['request']);
        for ($i = 1, $count = count($tokens); $i < $count; $i++) {
            if ($i < $count - 1 || !in_array($tokens[$i], $this->validServices)) {
                $ret[] = $tokens[$i];
            }
        }
        return $ret;
    }

    /**
     * @param array $request
     * @param array $args
     * @return null|object
     * @throws LudoDBClassNotFoundException
     */
    protected function getModel(array $request, $args = array())
    {
        $className = $this->getClassName($request);
        if (isset($className)) {
            $cl = $this->getReflectionClass($className);
            if (empty($args)) {
                return $cl->newInstance();
            } else {
                return $cl->newInstanceArgs($args);
            }
        }
        throw new LudoDBClassNotFoundException('Invalid request for: ' . $request['request'], 400);
    }

    private function getValidServices(array $request)
    {
        $className = $this->getClassName($request);
        if (isset($className)) {
            $servicesMethod = $this->getReflectionClass($className)->getMethod('getValidServices');
            return isset($servicesMethod) ? $servicesMethod->invoke(null) : array();
        }
        return array();
    }

    private function getReflectionClass($className)
    {
        $cl = new ReflectionClass($className);
        if (!$cl->implementsInterface('LudoDBService')) {
            throw new LudoDBClassNotFoundException('Invalid request for: ' . $className, 400);
        }
        return $cl;
    }

    /**
     * @param $request
     * @return string|null
     */
    private function getClassName($request)
    {
        $tokens = explode("/", $request['request']);
        return class_exists($tokens[0]) ? $tokens[0] : null;
    }

    protected function getServiceName($request)
    {
        $tokens = explode("/", $request['request']);
        $serviceName = $tokens[count($tokens) - 1];
        return in_array($serviceName, $this->validServices) ? $serviceName : 'read';
    }

    public function getValues()
    {
        $data = null;
        $caching = $this->model->cacheEnabled();
        if ($caching) {
            if ($this->ludoDBCache()->hasValue()) {
                $data = $this->ludoDBCache()->getCache();
            }
        }
        if (!isset($data)) {
            $data = $this->model->read();
            if ($caching && $this->model->getJSONKey()) {
                $this->ludoDBCache()->setCache($data)->commit();
            }
        }
        return $data;
    }

    /**
     * @return LudoDBCache
     */
    protected function ludoDBCache()
    {
        if (!isset($this->cacheInstance)) {
            $this->cacheInstance = new LudoDBCache($this->model);
        }
        return $this->cacheInstance;
    }

    public function clearCacheObject()
    {
        $this->cacheInstance = null;
    }
}
