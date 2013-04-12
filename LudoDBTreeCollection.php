<?php
/**
 * Tree collection
 * Date: 08.02.13
 * Time: 22:00
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/*
 * Tree collection
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * LudoDBTreeCollection
 *
 * Example of use of LudoDBTreeCollection
 *
 * <code>
 *  class TestNodesWithLeafs extends LudoDBTreeCollection implements LudoDBService{
 *      protected $config = array(
 *          "sql" => "select * from test_node order by parent,id",
 *          "childKey" => "children",
 *          "model" => "TestNode",
 *          "fk" => "parent",
 *          "pk" => "id",
 *          "static" => array(
 *              "type" => "node"
 *          ),
 *          "merge" => array(
 *              array(
 *                  "class" => "LeafNodes",
 *                  "fk" => "parent_node_id",
 *                  "pk" => "id"
 *              )
 *         )
 *      );
 *
 *      public function validateArguments($service, $arguments){
 *          return count($arguments) === 0;
 *      }
 *
 *      public function validateServiceData($service, $data){
 *          return true;
 *      }
 *
 *      public function getValidServices(){
 *          return array('read');
 *      }
 *
 *      public function shouldCache($service){
 *          return $service === "read";
 *      }
 *  }
 * </code>
 * @package LudoDB
 */
abstract class LudoDBTreeCollection extends LudoDBCollection
{

    /**
     * Return values in a tree structure
     * @return array
     */
    public function getValues()
    {
        $rows = parent::getValues();
        $rowReferences = array();
        $ret = array();

        $pk = $this->parser->getPK();
        $fk = $this->parser->getFK();
        $childKey = $this->parser->getChildKey();

        foreach ($rows as &$row) {
            $rowReferences[$row[$pk]] = & $row;
            if (isset($row[$fk])) {
                $parent = & $rowReferences[$row[$fk]];
                if (!isset($parent[$childKey])) {
                    $parent[$childKey] = array();
                }
                $parent[$childKey][] = & $row;
            } else {
                $ret[] = & $row;
            }
            unset($row[$fk]);

        }
        return $ret;
    }


}
