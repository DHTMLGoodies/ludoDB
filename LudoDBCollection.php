<?php
/**
 * LudoDB collection class
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * LudoDBCollection class.
 *
 * Example of implementation:
 *
 * With external config in JSON file:
 *
 * PHP:
 *
 * <code>
 *  class People extends LudoDBCollection
 *  {
 *      protected $JSONConfig = true;
 *  }
 * </code>
 *
 * JSON in JSONConfig/People.json:
 *
 * <code>
 * {
 *    "model": "Person",
 *    "sql": "select firstname, lastname, nick_name, p.zip,c.city from person p left join city c on c.zip = p.zip where p.zip=?",
 *    "columns": ["firstname", "lastname", "nick_name","zip", "city"]
 * }
 * </code>
 *
 * Example with internal PHP config:
 *
 * <code>
 * class DemoCities extends LudoDBCollection
 * {
 *    protected $config = array(
 *        "sql" => "select * from demo_city order by name",
 *        "model" => "DemoCity"
 *    );
 * }
 * </code>
 *
 * SQL can also be defined by creating a getSql method in the model
 *
 * Example:
 *
 * <code>
 * class Cities extends LudoDBCollection
 * {
 *
 *      public function getSql(){
 *          return is_numeric($this->arguments[0]) ?
 *              "select * from city where countryId=?" :
 *              "select c.* from city c,country o where c.countryId = o.countryId and o.country = ?";
 *      }
 *
 * }
 * </code>
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
abstract class LudoDBCollection extends LudoDBIterator
{
    /**
     * Lookup value to use when instantiating collection. This value
     * is used in join with config['constructBy']
     */
    protected $arguments;

    /**
     * Delete all database records involved in this collection. This method requires that the collection
     * has arguments. Use LudoDB::deleteTab
     */
    public function deleteRecords()
    {
        if (isset($this->arguments)) {
            $constructBy = $this->parser->getConstructorParams();
            if (isset($constructBy[0])) {
                $this->db->query("delete from " . $this->parser->getTableName() . " where " . $constructBy[0] . "=?", array($this->arguments[0]));
                $this->clearCache();
            }
        }
    }

    /**
     * Return instance of config parser for this LudoDBCollection
     * @return LudoDBCollectionConfigParser|LudoDBConfigParser
     */
    protected function getConfigParserInstance()
    {
        return new LudoDBCollectionConfigParser($this, $this->config);
    }

    /**
     * Return collection values. Use $start and $count to specify a limit query.
     * @param null $start
     * @param null $count
     * @return array
     */
    public function getValues($start = null, $count = null)
    {
        if(isset($start)){
            $this->sqlHandler()->setLimit($start, $count);
        }else{
            $this->sqlHandler()->clearLimit();
        }
        $model = $this->parser->getModel();
        if (isset($model)) {
            $ret = $this->getValuesUsingModel($model);
        } else {
            $ret = parent::getValues();
        }
        $this->mergeInOthers();
        return $ret;
    }

    /**
     * Return values for a row in the collection using a LudoDBModel as filter/parser. This will be called when
     * "model" is set in the config.
     * @param LudoDBModel $model
     * @return array
     */
    private function getValuesUsingModel(LudoDBModel $model)
    {
        $model->disableCommit();
        $ret = array();
        $key = $this->parser->getGroupBy();
        $staticValues = $model->parser->getStaticValues();

        $this->clearStoredRows();

        $i = 0;
        $j = 0;
        foreach ($this as $value) {
            if (!isset($columns)) $columns = array_keys($value);
            $model->clearValues();
            $model->setValues($value);
            $modelValues = $this->getValuesFromModel($model, $columns);
            $modelValues = array_merge($modelValues, $staticValues);
            if (isset($key) && isset($modelValues[$key])) {
                if (!isset($ret[$modelValues[$key]])) {
                    $ret[$modelValues[$key]] = array();
                    $j = 0;
                }
                $ret[$modelValues[$key]][$j] = $modelValues;
                $this->storeRow($ret[$modelValues[$key]][$j]);
            } else {
                $ret[$i] = $modelValues;
                $this->storeRow($ret[$i]);
                $i++;
            }
        }

        $model->enableCommit();

        return $ret;
    }

    /**
     * Return values for a row using LudoDBModel as a parser/filter.
     * @param LudoDBModel $model
     * @param array $columns
     * @return array
     */
    protected function getValuesFromModel($model, $columns)
    {
        return $model->getSomeValues($columns);
    }

    /**
     * Return
     * @return string
     */
    public function getCacheKey()
    {
        $ret = get_class($this);
        if (isset($this->arguments) && count($this->arguments)) {
            $ret .= "_" . implode("_", $this->arguments);
        }
        return $ret;
    }

    /**
     * Return values of a column as array
     * @param String $column
     * @return array
     */
    protected function getColumnValues($column)
    {
        $values = parent::getValues();
        $ret = array();
        foreach ($values as $value) {
            if (isset($value[$column])) {
                $ret[] = $value[$column];
            }
        }
        return $ret;
    }

    /**
     * Merge in values from other collections
     */
    protected function mergeInOthers()
    {
        $collectionsToMerge = $this->parser->getMerged();

        if (isset($collectionsToMerge)) {
            $childKey = $this->parser->getChildKey();
            $hideForeignKey = $this->parser->shouldHideForeignKeys();
            foreach ($collectionsToMerge as $collection) {
                if (isset($collection['fk'])) {
                    $fk = $collection['fk'];
                    if(isset($collection['childKey']))$childKey = $collection['childKey'];
                    if(isset($collection['hideForeignKeys']))$hideForeignKey = $collection['hideForeignKeys'];
                    $rows = $this->getRowsAssoc($collection['pk']);
                    $collectionObj = $this->getCollectionInstance($collection['class']);
                    $values = $collectionObj->getValues();

                    foreach ($values as & $row) {
                        if (isset($row[$fk])) {
                            $fkValue = $row[$fk];
                            if (!isset($rows[$fkValue][$childKey])) {
                                $rows[$fkValue][$childKey] = array();
                            }
                            $rows[$fkValue][$childKey][] = & $row;
                            if($hideForeignKey)unset($row[$fk]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Return a LudoDBCollection for a merged collection.
     * @param $className
     * @return LudoDBCollection
     */
    private function getCollectionInstance($className)
    {
        $r = new ReflectionClass($className);
        if (!$r->isSubclassOf("LudoDBCollection")) {
            throw new LudoDBInvalidConfigException("Merged class is not a valid LudoDBCollection object");
        }
        return $r->newInstance();
    }
}
