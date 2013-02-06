<?php
/**
 *
 * User: Alf Magne
 * Date: 06.02.13
 * Time: 08:57
 */
class LudoDBUtility
{

    public function dropAndCreate(array $classNames){
        $this->dropDatabaseTables($classNames);
        $this->createDatabaseTables($classNames);
    }

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
