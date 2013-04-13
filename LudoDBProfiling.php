<?php
/**
 *
 * http://localhost/LudoDBProfiling/Person/1/read/profile
 * User: Alf Magne Kalleland
 * Date: 18.02.13
 * Time: 20:48
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Class for profiling requests. This resources requires that you have the xhprof module enabled
 * on your Apache web server. It's also easiest to use when you have the mod rewrite module enabled
 * Syntax
 * LudoDBProfiling/resourceToProfile/arg1/arg2/resourceServiceToProfile/profile
 * i.e first argument is LudoDBProfiling and last argument should be profile. The arguments
 * in between is the request you want to profile, example
 *
 * <code>
 * http://localhost/LudoDBProfiling/Person/1/read/profile
 * </code>
 *
 * @package LudoDB
 */
class LudoDBProfiling implements LudoDBService
{

    /**
     * Constructor arguments
     * @var array
     */
    private $arguments;

    /**
     * Profiling name
     * @var string
     */
    private $name;

    /**
     * Start time
     * @var int
     */
    private $start;

    /**
     * Construct new instance
     */
    public function __construct()
    {
        $this->arguments = func_get_args();
    }

    /**
     * Returning "profile" as only valid service method
     * @return array
     */
    public function getValidServices()
    {
        return array("profile");
    }

    /**
     * Number of arguments has to be equal or bigger than 2 (resource + service name).
     * @param String $service
     * @param Array $arguments
     * @return bool
     */
    public function validateArguments($service, $arguments)
    {
        return count($arguments) >= 2;
    }

    /**
     * Start profiling.
     * @param array $data
     * @return array
     * @throws LudoDBException
     */
    public function profile($data = array())
    {
        $inDevelop = LudoDBRegistry::get('DEVELOP_MODE');
        if (!isset($inDevelop) || !$inDevelop) {
            throw new LudoDBException("Profiling can only be executed in develop mode. Use LudoDBRegistry::set('DEVELOP_MODE', true) to activate develop mode");
        }

        $request = implode("/", $this->arguments);
        $name = preg_replace("/[^0-9a-z]/si", "", $request);
        $this->start($name);

        $handler = new LudoDBRequestHandler();
        $result = json_decode($handler->handle(array(
            'request' => $request,
            'data' => $data
        )), true);

        if (!$result['success']) {
            throw new LudoDBException($result['message']);
        }

        $url = $this->end();

        return array(
            "result" => $url,
            "request" => $request,
            "data" => $data
        );
    }

    /**
     * Validate service data
     * @param string $service
     * @param array $data
     * @return bool
     */
    public function validateServiceData($service, $data)
    {
        return true;
    }

    /**
     * No caching
     * @param $service
     * @return bool
     */
    public function shouldCache($service)
    {
        return false;
    }

    /**
     * Return empty string on successful profiling.
     * @param String $service
     * @return String
     */
    public function getOnSuccessMessageFor($service)
    {
        return "";
    }

    /**
     * Start profiling.
     * @param $name
     * @throws LudoDBException
     */
    public function start($name)
    {
        $this->start = microtime(true);
        $this->name = $name;
        if (!function_exists("xhprof_enable")) {
            throw new LudoDBException("xhprof is not enabled on your server");
        }
        $flags = defined("XHPROF_FLAGS_CPU") ? XHPROF_FLAGS_CPU : 0;
        $flags += defined("XHPROF_FLAGS_MEMORY") ? XHPROF_FLAGS_MEMORY : 0;
        xhprof_enable($flags);
    }

    /**
     * Return elapsed time.
     * @return mixed
     */
    public function getTimeUsage()
    {
        return microtime(true) - $this->start;
    }

    /**
     * End profiling
     * @return null|string
     */
    public function end()
    {
        if (function_exists("xhprof_disable")) {
            $profilingData = xhprof_disable();
            $profilingRuns = new XHProfRuns_Default();
            $run_id = $profilingRuns->save_run($profilingData, $this->name);
            return "http://" . $_SERVER['HTTP_HOST'] . "/" . $this->getPath() . "/xhprof/xhprof_html/index.php?run=$run_id&source=" . $this->name;
        }
        return null;
    }

    /**
     * Return path to xhprof
     * @return string
     */
    private function getPath()
    {
        $path = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
        $countTokens = count(explode("/", $_SERVER['DOCUMENT_ROOT']));
        $path = array_slice($path, $countTokens);
        return implode("/", $path);
    }
}
