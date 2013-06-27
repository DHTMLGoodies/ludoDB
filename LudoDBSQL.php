<?php
/**
 * Class for building SQL queries in LudoDB
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * User: Alf Magne Kalleland
 * Date: 22.12.12
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
class LudoDBSql
{
    /**
     * Config array of LudoDBObject
     * @var array
     */
    private $config;
    /**
     * Constructor arguments of LudoDBObject instance.
     * @var array
     */
    private $arguments;
    /**
     * Internal reference to LudoDBObject
     * @var LudoDBObject
     */
    private $obj;
    /**
     * Internal string for limit queries
     * @var string
     */
    private $limit = "";

    /**
     * Internal constant representing value of delete columns.
     */
    const DELETED = '__DELETED__';

    /**
     * Config parser reference.
     * @var LudoDBConfigParser
     */
    private $configParser;

    private $sql;

    /**
     * Constructs new SQL handler for given LudoDBModel or LudoDBCollection.
     * @param LudoDBObject $obj
     */
    public function __construct(LudoDBObject $obj)
    {
        $this->obj = $obj;
        $this->configParser = $obj->configParser();
        $this->config = $obj->configParser()->getConfig();
        $this->arguments = $obj->getConstructorValues();
        $this->validate();
    }

    /**
     * Validate arguments
     */
    private function validate()
    {
        if (isset($this->arguments) && !is_array($this->arguments)) $this->arguments = array($this->arguments);
    }

    /**
     * Return "select" sql for it's LudoDBObject
     * @return string
     */
    public function getSql(){
        if(method_exists($this->obj, "getSql")){
            $sql = $this->obj->getSql();
            if(is_array($sql)){
                if(count($sql) === 2){
                    $this->arguments = $sql[1];
                }
                $sql = $sql[0];
            }
            return vsprintf($sql, $this->arguments).$this->limit;
        }else if (isset($this->config['sql'])) {
            return vsprintf($this->config['sql'], $this->arguments).$this->limit;
        }
        return $this->getCompiledSql().$this->limit;
    }

    public function getArguments(){
        return $this->arguments;
    }

    /**
     * Limit number of rows returned
     * @param $start
     * @param null $count
     */
    public function setLimit($start, $count = null){
        $this->limit = " limit $start" . (isset($count) ? ", ". $count : "");
    }

    /**
     * Clear limit of returned rows
     */
    public function clearLimit(){
        $this->limit = "";
    }

    /**
     * Return compiled sql from config of LudoDBObject. This will only be called when config
     * of LudoDBModel or LudoDBCollection does not contain any "sql" key/value.
     * @return string
     */
    private function getCompiledSql()
    {
        return "select " . $this->getColumns() . " from " . $this->getTables() . $this->getJoins() . $this->getOrderBy();
    }

    /**
     * Return columns for "select" sql
     * @return string
     */
    private function getColumns()
    {
        $ret = array();
        if ($this->configParser->hasColumns()) {
            $ret = $this->getMyColumns();
        }
        if (!$ret) {
            $ret = $this->configParser->getTableName() . ".*";
        }
        $ret .= $this->getColumnsToSelectFromJoins();
        return $ret;
    }

    /**
     * Get columns for table name in LudoDBModel/LudoDBCollection (my columns)
     * @return string
     */
    private function getMyColumns()
    {
        if (isset($this->config['columns'][0])) {
            return $this->getColumnsForCollectionSQL();
        }
        return implode(",", $this->configParser->getMyColumnsForSQL());
    }

    /**
     * Return column names for a colletion
     * @return string
     */
    private function getColumnsForCollectionSQL()
    {
        return $this->configParser->getTableName() . "." . implode("," . $this->configParser->getTableName() . ".", $this->configParser->getColumns());
    }

    /**
     * Return column names for joined tables.
     * @return string
     */
    private function getColumnsToSelectFromJoins()
    {
        $joins = $this->configParser->getColumnsToSelectFromJoins();
        if (count($joins)) {
            return "," . implode(",", $joins);
        }
        return '';
    }

    /**
     * Return name of tables involved in "select" SQL
     * @return string
     */
    private function getTables()
    {
        return implode(",", array_merge(array($this->configParser->getTableName()), $this->configParser->getTableNamesFromJoins()));
    }

    /**
     * Get "join" for "select" SQL.
     * @return string
     */
    private function getJoins()
    {
        $ret = $this->configParser->getJoinsForSQL();
        $constructBy = $this->configParser->getConstructorParams();
        $pdo = LudoDB::hasPDO();
        if (isset($constructBy)) {
            for ($i = 0, $count = count($this->arguments); $i < $count; $i++) {
                $ret[] = $this->getTableAndColumn($constructBy[$i]) . "=" . ($pdo ? "?" : "'" . $this->arguments[$i] . "'");
            }
        }
        if (count($ret)) {
            return " where " . implode(" and ", $ret);
        }
        return '';
    }

    /**
     * Return column name prefixed by tableName.
     * @param $column
     * @return string
     */
    private function getTableAndColumn($column)
    {
        return strstr($column, ".") ? $column : $this->configParser->getTableName() . "." . $column;
    }

    /**
     * Get order by for "select" SQL
     * @return string
     */
    private function getOrderBy()
    {
        $orderBy = $this->configParser->getOrderBy();
        return isset($orderBy) ? ' order by ' . $orderBy : '';
    }

    /**
     * Return "create" table SQL.
     * @return string
     */
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
                    $col = $name . " " . $type['db'];
                    if (isset($type['default'])) {
                        $col .= " default ?";
                    }
                    $columns[] = $col;

                    if (isset($type['references'])) {
                        $columns[] = "FOREIGN KEY(" . $name . ") REFERENCES " . $type['references'];
                    }

                }
            }
        }
        $sql .= implode(",", $columns) . ")";
        return $sql;
    }

    /**
     * Return "insert" SQL.
     * @return mixed
     */
    public function getInsertSQL()
    {
        $table = $this->configParser->getTableName();
        $data = $this->obj->getUncommitted();

        if (LudoDB::hasPDO()) {
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

    /**
     * Return "insert" SQL for the PDO adapter (prepared statements)
     * @param $data
     * @return mixed
     */
    private function getPDOInsert($data)
    {
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

    /**
     * Return "update" SQL.
     * @return string
     */
    public function getUpdateSql()
    {
        return "update " . $this->obj->configParser()->getTableName() . " set " . $this->getUpdatesForSql($this->obj->getUncommitted()) . " where " . $this->obj->configParser()->getIdField() . " = '" . $this->obj->getId() . "'";
    }

    /**
     * Return columns to update for "update" SQL.
     * @param $updates
     * @return string
     */
    private function getUpdatesForSql($updates)
    {
        $ret = array();
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                $ret[] = $key . "=?";
            }
        }
        return implode(",", $ret);
    }

    /**
     * Convert SQL string for prepared statement to standard SQL statement with values escaped(safe values).
     * @param $sql
     * @param array $params
     * @return string
     */
    public static function fromPrepared($sql, $params = array())
    {
        if (!strstr($sql, "?")) return $sql;

        $sql = str_replace("?", "'%s'", $sql);
        $sql = str_replace("''%s''", "'%s'", $sql);
        $db = LudoDB::getInstance();
        for ($i = 0, $count = count($params); $i < $count; $i++) {
            $params[$i] = $db->escapeString($params[$i]);
        }
        $sql = vsprintf($sql, $params);
        return $sql;
    }
}
