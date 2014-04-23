<?php
/**
 * Class used during Development/Debugging.
 * User: Alf Magne
 * Date: 06.02.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * LudoDBUtility class with development methods for manipulation of database tables.
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBUtility
{

    /**
     * Array of LudoDBModel instances
     * @var array
     */
    private $instances = array();

    /**
     * Drop and create database tables for the given class names
     * $classNames is an array of LudoDBModel sub classes.
     * This method is useful during development since it will check
     * table references(defined in config) and drop and create the tables
     * in the right order
     * @param array $classNames
     */
    public function dropAndCreate(array $classNames){
        $this->dropDatabaseTables($classNames);
        $this->createDatabaseTables($classNames);
    }

    /**
     * Drop the database tables of the given classes.
     * @param array $classNames
     */
    public function dropDatabaseTables(array $classNames){
        $classes = array_reverse($this->getClassesRearranged($classNames));
        foreach($classes as $class){
            $inst = $this->getClassInstance($class);
            $inst->drop()->yesImSure();
        }
    }

    /**
     * Create database tables for the given classes. $classNames is an array of
     * valid LudoDBModel class names.
     * @param array $classNames
     */
    public function createDatabaseTables(array $classNames)
    {
        $classes = $this->getClassesRearranged($classNames);
        foreach($classes as $class){
            $inst = $this->getClassInstance($class);
            if(!$inst->exists())$inst->createTable();
        }
    }

    /**
     * Returns database create syntax (MySQL) for the selected tables
     */
    public function getDatabaseCreate(array $classNames){
        $ret = array();
        $classes = $this->getClassesRearranged($classNames);
        foreach($classes as $class){
            $inst = $this->getClassInstance($class);
            $ret[] = $inst->getSQLCreate();

        }
        return $ret;
    }

    /**
     * Return database tables for given class names
     * @param array $classNames
     * @return array
     */
    protected function getLudoDBModelTables(array $classNames)
    {
        $ret = array();
        foreach ($classNames as $className) {
            if (class_exists($className)) {
                $r = new ReflectionClass($className);
                if ($r->isSubclassOf('LudoDBModel')) {
                    $ret[] = $className;
                }
            }
        }
        return $ret;
    }

    /**
     * Return classes in right order based on dependencies (foreign keys)
     * @param array $classNames
     * @return array
     */
    protected function getClassesRearranged(array $classNames)
    {
        $classes = $this->getLudoDBModelTables($classNames);
        $classes = $this->withDuplicatesRemoved($classes);

        $ret = $classes;
        $tableNames = $this->getTableNames($classes);
        $itemFound = true;
        $counter = 0;
        $max = count($classNames);
        while ($itemFound && $counter < $max) {
            $itemFound = false;
            $counter++;
            foreach ($classes as $className) {
                $cl = $this->getClassInstance($className);
                $references = $this->getReferencedTables($cl);
                $tableName = $cl->configParser()->getTableName();
                if (!empty($references)) {
                    foreach ($references as $reference) {
                        if (in_array($reference, $tableNames)) {
                            $index = array_search($reference, $tableNames);
                            $indexThis = array_search($tableName, $tableNames);
                            if ($index !== FALSE && $indexThis !== FALSE && $index > $indexThis) {
                                array_splice($tableNames, $index+1, 0, array($tableName));
                                array_splice($ret, $index+1, 0, array($className));

                                array_splice($tableNames, $indexThis, 1);
                                array_splice($ret, $indexThis, 1);
                                $itemFound = true;
                            }
                        }
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Remove duplicate class names, i.e. classes using the same database table.
     * @param array $classNames
     * @return array
     */
    private function withDuplicatesRemoved(array $classNames){
        $tables = array();
        $ret = array();
        foreach($classNames as $className){
            $instance = $this->getClassInstance($className);
            $tableName = $instance->configParser()->getTableName();
            if(!in_array($tableName, $tables)){
                $ret[] = $className;
                $tables[] = $tableName;
            }
        }
        return $ret;
    }

    /**
     * Return table names for given classes.
     * @param $classNames
     * @return array
     */
    private function getTableNames($classNames)
    {
        $ret = array();
        foreach ($classNames as $className) {
            $ret[] = $this->getClassInstance($className)->configParser()->getTableName();
        }
        return $ret;
    }


    /**
     * Get tables referenced by a model, i.e. foreign keys.
     * @param LudoDBModel $model
     * @return array
     */
    private function getReferencedTables(LudoDBModel $model)
    {
        $ret = array();
        $references = $model->configParser()->getTableReferences();
        foreach ($references as $reference) {
            $ret[] = $reference['table'];
        }
        return $ret;
    }




    /**
     * Return instance of a LudoDBModel
     * @param $name
     * @return LudoDBModel
     */
    private function getClassInstance($name)
    {
        if (!isset($this->instances[$name])) {
            $r = new ReflectionClass($name);
            $this->instances[$name] = $r->newInstance();
        }
        return $this->instances[$name];
    }
    // TODO implement
    /**
     * To be implemented
     * @param array $classNames
     */
    public function validateConfigsOf(array $classNames){

    }
    // TODO implement
    /**
     * To be implemented.
     */
    public function getAllAvailableServices(){

    }

    public function getTableDefinition($className){
        $class = $this->getClassInstance($className);
    }
}
