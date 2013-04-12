<?php
/**
 * LudoDB collection class
 * User: Alf Magne Kalleland
 * Date: 19.12.12
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
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
     * @param LudoDBModel $model
     * @param array $columns
     * @return array
     */
    protected function getValuesFromModel($model, $columns)
    {
        return $model->getSomeValues($columns);
    }

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
