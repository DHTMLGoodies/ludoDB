<?php
/**
 * Tree collection
 * User: Alf Magne Kalleland
 * Date: 08.02.13
 * Time: 22:00
 */
abstract class LudoDBTreeCollection extends LudoDBCollection
{

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

        }
        return $ret;
    }






}
