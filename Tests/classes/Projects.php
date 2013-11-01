<?php
/**
 *
 * User: Alf Magne
 * Date: 16.10.13
 * Time: 13:22
 */
class Projects extends LudoDBCollection implements LudoDBService
{
    protected $config = array(
        "model" => "Project",
        "sql" => "select * from project order by title",
        "columns" => array("id","title","description")
    );

    public function validateServiceData($service, $data){
        return empty($data);
    }

    public function validateArguments($service, $arguments){
        return empty($arguments);
    }

    public function getValidServices(){
        return array("read");
    }

    public function read(){
        $pr = new Project();

        if($pr->exists()){
            $pr->drop()->yesImSure();
        }
        $pr->createTable();
        #if(!$pr->exists()){
            $pr->drop()->yesImSure();
            $pr->createTable();
        #}
        return parent::read();
    }

}
