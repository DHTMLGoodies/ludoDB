<?php
/**
 * Tree collection
 * User: Alf Magne Kalleland
 * Date: 08.02.13
 * Time: 22:00
 */
abstract class LudoDBTreeCollection extends LudoDBCollection
{
    private $rows;

    public function getValues(){
        $rows = parent::getValues();
        $rowReferences = array();
        $ret = array();

        $pk = $this->parser->getPK();
        $fk = $this->parser->getFK();
        $childKey = $this->parser->getChildKey();

        foreach($rows as &$row){
            $rowReferences[$row[$pk]] = &$row;
            if(isset($row[$fk])){
                $parent = & $rowReferences[ $row[$fk] ];
                if(!isset($parent[$childKey])){
                    $parent[$childKey] = array();
                }
                $parent[$childKey][] = &$row;
            }else{
                $ret[] = &$row;
            }
            unset($row[$fk]);
            $this->rows[] = &$row;
        }
        $this->mergeInOthers();
        return $ret;
    }

    private function mergeInOthers(){
        $merged = $this->parser->getMerged();
        if(isset($merged)){
            $childKey = $this->parser->getChildKey();
            foreach($merged as $merge){
                
                if(isset($merge['fk'])){
                    $fk = $merge['fk'];
                    $rows = $this->getRowsAssoc($merge['pk']);

                    $mergeObj = $this->getMergeCollection($merge['class']);
                    $values = $mergeObj->getValues();
                    foreach($values as & $row){
                        $fkValue = $row[$fk];

                        if(!isset($rows[$fkValue][$childKey])){
                            $rows[$fkValue][$childKey] = array();
                        }
                        $rows[$fkValue][$childKey][] = & $row;

                    }
                }
            }
        }
    }

    /**
     * @param $className
     * @return LudoDBCollection
     */
    private function getMergeCollection($className){
        $r = new ReflectionClass($className);
        return $r->newInstance();
    }

    private function getRowsAssoc($key){
        $rows = $this->getRows();
        $ret = array();
        foreach($rows as & $row){
            if(isset($row[$key])){
                $ret[$row[$key]] = & $row;
            }
        }
        return $ret;
    }

    /**
     * Returns reference to all tree nodes as numeric array
     * @return Array
     */
    public function getRows(){
        return $this->rows;
    }
}
