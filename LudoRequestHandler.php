<?php
/**
 * Request handler class for Front End Controller. This class will handle requests sent
 * by Views and pass them to the correct LudoDBObjects.
 * User: Alf Magne Kalleland
 * Date: 13.01.13
 * Time: 16:24
 */
class LudoRequestHandler
{
    private $request;
    /**
     * @var LudoDBObject
     */
    private $model;
    private $action;
    private $jsonCacheInstance;

    public function __construct()
    {

    }

    public function handle($request)
    {
        $this->request = $request;
        $this->model = $this->getModel($request, $this->getArguments($request));
        $this->action = $this->getAction($this->request);

        switch ($this->action) {
            case 'read':
                return $this->toJSON($this->getValues());

        }
        return "";
    }

    private function toJSON(array $data)
    {
        $ret = array(
            'success' => true,
            'message' => '',
            'data' => $data
        );
        if (LudoDB::isLoggingEnabled()) {
            $ret['log'] = array(
                'time' => LudoDB::getElapsed(),
                'queries' => LudoDB::getQueryCount()
            );
        }

        return json_encode($ret);
    }

    private function getArguments(array $request)
    {
        return isset($request['data']) ? is_array($request['data']) ? $request['data'] : array($request['data']) : null;
    }

    /**
     * @param array $request
     * @param array $args
     * @return null|object
     * @throws Exception
     */
    protected function getModel(array $request, $args = array())
    {
        $className = $this->getClassName($request);
        if (isset($className)) {
            try {
                $cl = new ReflectionClass($className);
                if (empty($args)) {
                    return $cl->newInstance();
                } else {
                    return $cl->newInstanceArgs($args);
                }
            } catch (Exception $e) {
                throw new LudoDBClassNotFoundException('Class not found: ' . $className);
            }
        }
        return null;
    }
    /**
     * @param $request
     * @return string|null
     */
    private function getClassName($request)
    {
        if (isset($request['model'])) return $request['model'];
        return isset($request['form']) ? $request['form'] : null;
    }

    protected function getAction($request)
    {
        return isset($request['action']) ? strtolower($request['action']) : null;
    }



    public function getValues()
    {
        $data = null;
        $caching = $this->model->JSONCacheEnabled();
        if ($caching) {
            if ($this->cache()->hasValue()) {
                $data = $this->cache()->getCache();
            }
        }
        if (!isset($data)) {
            $data = $this->model->getValues();
            if ($caching && $this->model->getJSONKey()) {
                $this->cache()->setCache($data)->commit();
            }
        }
        return $data;
    }

    /**
     * @return LudoDBCache
     */
    protected function cache()
    {
        if (!isset($this->jsonCacheInstance)) {
            $this->jsonCacheInstance = new LudoDBCache($this->model);
        }
        return $this->jsonCacheInstance;
    }

    public function clearCacheObject()
    {
        $this->jsonCacheInstance = null;
    }
}
