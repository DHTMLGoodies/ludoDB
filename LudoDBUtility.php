<?php
/**
 * Class used during Development/Debugging.
 * User: Alf Magne
 * Date: 06.02.13
 * Time: 08:57
 */
class LudoDBUtility
{

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

    public function createDatabaseTables(array $classNames)
    {
        $classes = $this->getClassesRearranged($classNames);
        foreach($classes as $class){
            $inst = $this->getClassInstance($class);
            if(!$inst->exists())$inst->createTable();
        }
    }

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
                                array_splice(&$tableNames, $index+1, 0, array($tableName));
                                array_splice(&$ret, $index+1, 0, array($className));

                                array_splice(&$tableNames, $indexThis, 1);
                                array_splice(&$ret, $indexThis, 1);

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
     * @param $classNames
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

    private function getTableNames($classNames)
    {
        $ret = array();
        foreach ($classNames as $className) {
            $ret[] = $this->getClassInstance($className)->configParser()->getTableName();
        }
        return $ret;
    }


    private function getReferencedTables(LudoDBModel $model)
    {
        $ret = array();
        $references = $model->configParser()->getTableReferences();
        foreach ($references as $reference) {
            $ret[] = $reference['table'];
        }
        return $ret;
    }


    private $instances = array();

    /**
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
}
