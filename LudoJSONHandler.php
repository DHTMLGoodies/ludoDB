<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alf Magne
 * Date: 30.01.13
 * Time: 15:06
 * To change this template use File | Settings | File Templates.
 */
class LudoJSONHandler
{
    private $model;
    private $jsonCacheInstance;

    public function __construct(LudoDBObject $model)
    {
        $this->model = $model;
    }

    public function asJSON()
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
            if (LudoDB::isLoggingEnabled()) {
                $data['__log'] = array(
                    'time' => LudoDB::getElapsed(),
                    'queries' => LudoDB::getQueryCount()
                );
            }
            if ($caching && $this->model->getJSONKey()) {
                $this->cache()->setCache($data)->commit();
            }
        }
        return json_encode($data);
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
