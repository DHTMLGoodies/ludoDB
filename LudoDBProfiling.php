<?php
/**
 * Class for profiling requests. This resources requires that you have the xhprof module enabled
 * on your Apache web server. It's also easiest to use when you have the mod rewrite module enabled
 * Syntax
 * LudoDBProfiling/resourceToProfile/arg1/arg2/resourceServiceToProfile/profile
 * i.e first argument is LudoDBProfiling and last argument should be profile. The arguments
 * in between is the request you want to profile, example
 * http://localhost/LudoDBProfiling/Person/1/read/profile
 * User: Alf Magne Kalleland
 * Date: 18.02.13
 * Time: 20:48
 */
class LudoDBProfiling implements LudoDBService
{

    private $arguments;
    private $name;
    private $start;

    public function __construct(){
        $this->arguments = func_get_args();
    }
    public function getValidServices(){
        return array("profile");
    }

    public function validateArguments($service, $arguments){
        return count($arguments) >= 2;
    }

    public function profile(){

        $request = implode("/", $this->arguments);
        $this->start(preg_replace("/[^0-9a-z]/si", "", $request));

        $handler = new LudoDBRequestHandler();
        $handler->handle($request);

        return $this->end();
    }

    public function validateServiceData($service, $data){
        return true;
    }
    public function shouldCache($service){
        return false;
    }

    public function getOnSuccessMessageFor($service){
        return "";
    }

    public function start($name){
        $this->start = microtime(true);
        $this->name = $name;
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    }

    public function getTimeUsage(){
        return microtime(true) - $this->start;
    }

    public function end(){
        $profilingData = xhprof_disable();
        $profilingRuns = new XHProfRuns_Default();
        $run_id = $profilingRuns->save_run($profilingData, $this->name);
        return "http://localhost:8080/xhprof/xhprof_html/index.php?run=$run_id&source=". $this->name;
    }
}
