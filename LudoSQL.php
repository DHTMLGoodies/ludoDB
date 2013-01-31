<?php
/**
 * User: Alf Magne Kalleland
 * Date: 22.12.12
 * Time: 00:19
 */
class LudoSQL
{
    private $config;
    private $constructorValues;
    private $obj;
    const DELETED = '__DELETED__';

    /**
     * @var LudoDBObject
     */
    private $configParser;

    public function __construct(LudoDBObject $obj)
    {
        $this->obj = $obj;
        $this->configParser = $obj->configParser();
        $this->config = $obj->configParser()->getConfig();
        $this->constructorValues = $obj->getConstructorValues();
        $this->validate();
    }

    private function validate()
    {
        if (isset($this->constructorValues) && !is_array($this->constructorValues)) $this->constructorValues = array($this->constructorValues);
    }

    public function getSql()
    {
        if (isset($this->config['sql'])) {
            return vsprintf($this->config['sql'], $this->constructorValues);
        } else {
            return $this->getCompiledSql();
        }
    }

    private function getCompiledSql()
    {
        return "select " . $this->getColumns() . " from " . $this->getTables() . $this->getJoins() . $this->getOrderBy();
    }

    private function getColumns()
    {
        $ret = array();
        if ($this->configParser->hasColumns()) {
            $ret = $this->getColumnsForSql();
        }
        if (!$ret) {
            $ret = $this->configParser->getTableName() . ".*";
        }
        $ret .= $this->getColumnsFromJoins();
        return $ret;
    }

    private function getColumnsForSql()
    {
        if (isset($this->config['columns'][0])) {
            return $this->getColumnsForCollectionSQL();
        }
        return implode(",", $this->configParser->getMyColumnsForSQL());
    }

    private function getColumnsForCollectionSQL()
    {
        return $this->configParser->getTableName() . "." . implode("," . $this->configParser->getTableName() . ".", $this->configParser->getColumns());
    }

    private function getColumnsFromJoins()
    {
        $joins = $this->configParser->getColumnsFromJoins();
        if (count($joins)) {
            return "," . implode(",", $joins);
        }
        return '';
    }

    private function getTables()
    {
        return implode(",", array_merge(array($this->configParser->getTableName()), $this->configParser->getTableNamesFromJoins()));
    }

    private function getJoins()
    {
        $ret = $this->configParser->getJoinsForSQL();
        $constructBy = $this->configParser->getConstructorParams();
        $pdo = LudoDB::hasPDO();
        if (isset($constructBy)) {
            for ($i = 0, $count = count($this->constructorValues); $i < $count; $i++) {
                $ret[] = $this->getTableAndColumn($constructBy[$i]) . "=" . ($pdo ? "?" : "'" . $this->constructorValues[$i] . "'");
            }
        }
        if (count($ret)) {
            return " where " . implode(" and ", $ret);
        }
        return '';
    }

    private function getTableAndColumn($column)
    {
        return strstr($column, ".") ? $column : $this->configParser->getTableName() . "." . $column;
    }

    private function getOrderBy()
    {
        $orderBy = $this->configParser->getOrderBy();
        return isset($orderBy) ? ' order by ' . $orderBy : '';
    }

    public function getCreateTableSql()
    {
        $sql = "create table " . $this->configParser->getTableName() . "(";
        $columns = array();
        $configColumns = $this->configParser->getColumns();
        foreach ($configColumns as $name => $type) {
            if (!$this->configParser->isExternalColumn($name)) {
                if (is_string($type)) {
                    $columns[] = $name . " " . $type;
                } else {
                    $columns[] = $name . " " . $type['db'];
                }
            }
        }
        $sql .= implode(",", $columns) . ")";
        return $sql;
    }

    public function getDeleteSQL()
    {
        $sql = "delete from " . $this->configParser->getTableName() . " where ";
        $where = array();
        $configParams = $this->configParser->getConstructorParams();
        for ($i = 0, $count = count($this->constructorValues); $i < $count; $i++) {
            $val = $this->constructorValues[$i];
            if (is_string($val)) {
                $val = "'" . $val . "'";
            }
            $where[] = $configParams[$i] . "=" . $val;
        }
        $sql .= implode(" and ", $where);
        return $sql;
    }

    public function getInsertSQL()
    {

        $table = $this->configParser->getTableName();
        $data = $this->obj->getUncommitted();

        if(LudoDB::hasPDO()){
            return $this->getPDOInsert($data);
        }
        if (!isset($data)) $data = array(
            $this->obj->configParser()->getIdField() => self::DELETED
        );
        $sql = "insert into " . $table . "(" . implode(",", array_keys($data)) . ")";
        $sql .= "values('" . implode("','", array_values($data)) . "')";
        $sql = str_replace("'" . self::DELETED . "'", "null", $sql);

        return $sql;
    }

    private function getPDOInsert($data){
        $table = $this->configParser->getTableName();

        if (!isset($data)) $data = array(
            $this->obj->configParser()->getIdField() => null
        );

        $keys = array_keys($data);
        $sql = "insert into " . $table . "(" . implode(",", $keys) . ")";

        $values = implode(",", array_fill(0, count($keys), '?'));
        $sql .= "values(" . $values . ")";
        $sql = str_replace("'" . self::DELETED . "'", "null", $sql);

        return $sql;
    }

    public function getUpdateSql()
    {
        return "update " . $this->obj->configParser()->getTableName() . " set " . $this->getUpdatesForSql($this->obj->getUncommitted()) . " where " . $this->obj->configParser()->getIdField() . " = '" . $this->obj->getId() . "'";
    }

    private function getUpdatesForSql($updates)
    {
        $ret = array();
        foreach ($updates as $key => $value) {
            $ret[] = $key . "=" . ($value === self::DELETED ? 'NULL' : "'" . $value . "'");
        }
        return implode(",", $ret);
    }

    public static function fromPrepared($sql, $params = array()){
        $sql = str_replace(",?", ",'%s'", $sql);
        $db = LudoDB::getInstance();
        for($i=0,$count = count($params);$i<$count;$i++){
            $params[$i] = $db->escapeString($params[$i]);
        }

        $sql = vsprintf($sql, $params);
        return $sql;
    }
}
