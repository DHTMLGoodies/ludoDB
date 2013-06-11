<?php
/**
 * Parser for LudoDBObject
 * User: Alf Magne
 * Date: 10.01.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Parser of config for LudoDBObject classes. Instances of this class are created automatically by LudoDB.
 * @package LudoDB
 */
class LudoDBConfigParser
{
    /**
     * Config object
     * @var array|null
     */
    protected $config;
    /**
     * Internal cache of relations between a column method and a column
     * @var array
     */
    private static $columnMappingCache = array();

    /**
     * Mapping of alias names of columns - for fast lookup
     * @var array
     */
    private $aliasMapping = array();

    /**
     * Reference to the LudoDBObject the parser is assigned to handle
     * @var LudoDBObject
     */
    private $obj;

    /**
     * Instances of class
     * @var array
     */
    private static $extensionClasses = array();

    /**
     * Custom constructor params
     * @var array
     */
    private $customConstructorParams;

    /**
     * Cache of names of external columns.
     * @var array
     */
    private $externalCache = array();

    /**
     * Array of columns of LudoDBObject for this parser.
     * @var array
     */
    private $myColumns;
    /**
     * Constructs new parser.
     * @param LudoDBObject $obj
     * @param array $config
     */
    public function __construct(LudoDBObject $obj, $config = array())
    {
        $this->obj = $obj;
        $this->parseConfig($config);
    }

    /**
     * Parse config.
     * @param array $config
     */
    private function parseConfig($config)
    {
        $this->config = $this->getValidConfig($config);
        $parent = $this->getExtends();
        if (isset($parent)) {
            $this->config = $this->getMergedConfigs($parent->configParser()->getConfig(), $this->config);
        }
        $this->mapColumnAliases();
    }


    /**
     * Return a LudoDBObject instance this LudoDBObject object extends. (Only when this class extends another LudoDBObject).
     * @return LudoDBObject
     */
    private function getExtends()
    {
        $className = $this->getProperty('extends');
        if (!isset($className)) {
            $parent = get_parent_class($this->obj);
            if ($parent !== 'LudoDBModel' && $parent != 'LudoDBCollection' && $parent != 'LudoDBTreeCollection') {
                $className = $parent;
            }
        }
        if (!isset($className)) return null;
        if (!isset(self::$extensionClasses[$className])) {
            self::$extensionClasses[$className] = new $className;
        }
        return self::$extensionClasses[$className];
    }

    /**
     * Return config from JSON file as array.
     * @return array
     * @throws Exception
     */
    private function getConfigFromFile()
    {
        $location = $this->getPathToJSONConfig();
        if (file_exists($location)) {
            $content = file_get_contents($location);
            return JSON_decode($content, true);
        } else {
            throw new Exception("Could not load config file $location");
        }
    }

    /**
     * Return path to JSON config file.
     * @return string
     */
    private function getPathToJSONConfig()
    {
        return $this->getFileLocation() . "/JSONConfig/" . get_class($this->obj) . ".json";
    }

    /**
     * Return path to JSON file for default data (name is ClassName.data.json).
     * @return string
     */
    public function getPathToJsonConfigDefaultData()
    {
        return $this->getFileLocation() . "/JSONConfig/" . get_class($this->obj) . ".data.json";
    }

    /**
     * Return file location of this class.
     * @return string
     */
    public function getFileLocation()
    {
        $obj = new ReflectionClass($this->obj);
        return dirname($obj->getFilename());
    }

    /**
     * Save column alias to cache.
     */
    private function mapColumnAliases()
    {
        if (isset($this->config['columns'])) {
            foreach ($this->config['columns'] as $colName => $col) {
                if (is_array($col) && isset($col['alias'])) {
                    $this->aliasMapping[$col['alias']] = $colName;
                }
            }
        }
    }

    /**
     * Return config of this LudoDBObject with config of parent LudoDBObject.
     * @param $config1
     * @param $config2
     * @return array
     */
    private function getMergedConfigs($config1, $config2)
    {
        if (!is_array($config1) or !is_array($config2)) {
            return $config2;
        }
        foreach ($config2 as $sKey2 => $sValue2) {
            $config1[$sKey2] = $this->getMergedConfigs(@$config1[$sKey2], $sValue2);
        }
        return $config1;
    }

    /**
     * Return config
     * @return array|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return input config validated.
     * @param $config
     * @return array
     */
    private function getValidConfig($config)
    {
        if ($this->obj->hasConfigInExternalFile()) {
            $config = $this->getConfigFromFile();
        }
        if (isset($config['sql']) && !LudoDB::hasPDO()) {
            $config['sql'] = str_replace("?", "'%s'", $config['sql']);
            $config['sql'] = str_replace("''", "'", $config['sql']);
        }
        if (!isset($config['constructBy']) && isset($config['idField'])) {
            $config['constructBy'] = array($config['idField']);
        }
        if (isset($config['constructBy']) && !is_array($config['constructBy'])) {
            $config['constructBy'] = array($config['constructBy']);
        }
        if (!isset($config['static'])) {
            $config['static'] = array();
        }
        return $config;
    }

    /**
     * Return table name for LudoDB
     * @return mixed
     */
    public function getTableName()
    {
        return $this->config['table'];
    }

    /**
     * Return constructor params.
     * @return array|null
     */
    public function getConstructorParams()
    {
        if (isset($this->customConstructorParams)) return $this->customConstructorParams;
        return isset($this->config['constructBy']) ? $this->config['constructBy'] : null;
    }

    /**
     * Return name of external class for column.
     * @param $column
     * @return null
     */
    public function externalClassNameFor($column)
    {
        return $this->getColumnProperty($column, 'class');
    }

    /**
     * Return a column property from config, example: "db", "references", "alias"
     * @param $name
     * @param $property
     * @return null
     */
    private function getColumnProperty($name, $property)
    {
        if ($ret = $this->getExternalClassProperty($name, $property)) {
            return $ret;
        }
        $col = $this->getColumn($name);
        return isset($col) && is_array($col) && isset($col[$property]) ? $col[$property] : null;
    }

    /**
     * Return config property from external class referenced in config of the LudoDBObject of this class.
     * @param $column
     * @param $property
     * @return null
     */
    private function getExternalClassProperty($column, $property)
    {
        if (isset($this->config['classes']) && isset($this->config['classes'][$column])) {
            $cl = $this->config['classes'];
            return isset($cl[$column]) && isset($cl[$column][$property]) ? $cl[$column][$property] : null;
        }
        return null;
    }


    /**
     * Returns true if given column is from external LudoDBObject
     * @param $name
     * @return mixed
     */
    public function isExternalColumn($name)
    {
        if (!isset($this->externalCache[$name])) {
            $col = $this->getColumn($name);
            $this->externalCache[$name] = isset($col) && is_array($col) && !isset($col['db']);
        }
        return $this->externalCache[$name];

    }

    /**
     * Return name of id field.
     * @return string
     */
    public function getIdField()
    {
        return isset($this->config['idField']) ? $this->config['idField'] : 'id';
    }

    /**
     * Returns true if auto increment of id is specified in config.
     * @return bool
     */
    public function isIdAutoIncremented()
    {
        return strstr($this->getDbDefinition($this->getIdField()), 'auto_increment') ? true : false;
    }

    /**
     * Return db definition of a column, example: "int auto_increment not null primary key"
     * @param $column
     * @return mixed
     */
    private function getDbDefinition($column)
    {
        $col = $this->config['columns'][$column];
        return is_array($col) ? $col['db'] : $col;
    }

    /**
     * Return "set" property of a column in config, i.e. name of set method.
     * @param $column
     * @return null
     */
    public function getSetMethod($column)
    {
        return $this->getColumnProperty($column, 'set');
    }

    /**
     * Return "get" property of a column in config, i.e. name of get method.
     * @param $column
     * @return null|string
     */
    public function getGetMethod($column)
    {
        $column = $this->getInternalColName($column);
        $method = $this->getColumnProperty($column, 'get');
        return isset($method) ? $method : 'getValues';
    }

    /**
     * Return foreign key for a column if exists.
     * @param $column
     * @return null
     */
    public function foreignKeyFor($column)
    {
        $column = $this->getInternalColName($column);
        return $this->getColumnProperty($column, 'fk');
    }

    /**
     * Return indexed columns from config.
     * @return null
     */
    public function getIndexes()
    {
        return $this->getProperty('indexes');
    }

    /**
     * Get default data for database table.
     * @return array|null
     */
    public function getDefaultData()
    {
        $ret = $this->getProperty('data');
        if (is_string($ret)) {
            $file = $this->getPathToJsonConfigDefaultData();
            if (file_exists($file)) {
                return json_decode(file_get_contents($file), true);
            } else {
                return null;
            }
        } else {
            return $ret;
        }
    }

    /**
     * Return array of joined tables
     * @return null|array
     */
    private function getJoins()
    {
        return $this->getProperty('join');
    }


    /**
     * Return name of my columns prefixed by tableName + .
     * @return array
     */
    public function getMyColumnsForSQL()
    {
        if (!isset($this->myColumns)) {
            $columns = $this->getColumns();
            $ret = array();
            foreach ($columns as $col => $value) {
                if (!$this->isExternalColumn($col)) {
                    $ret[] = $this->getTableName() . "." . $col;
                }
            }
            $this->myColumns = $ret;
        }

        return $this->myColumns;
    }

    /**
     * Return array of names of joined columns prefixed by their table names.
     * @return array
     */
    public function getJoinsForSQL()
    {
        $joins = $this->getJoins();
        $ret = array();
        if (isset($joins)) {
            foreach ($joins as $join) {
                $ret[] = $this->getTableName() . "." . $join['fk'] . "=" . $join['table'] . "." . $join['pk'];
            }
        }
        return $ret;
    }

    /**
     * Return columns to select from joined tables prefixed by their table names.
     * @return array
     */
    public function getColumnsToSelectFromJoins()
    {
        $ret = array();
        $joins = $this->getJoins();
        if (isset($joins)) {
            foreach ($joins as $join) {
                foreach ($join['columns'] as $col) {
                    $ret[] = $join['table'] . "." . $col;
                }
            }
        }
        return $ret;
    }

    /**
     * Return array of columns from config.
     * @return array
     */
    public function getColumns()
    {
        return $this->getProperty('columns');
    }

    /**
     * Return public column name. If "alias" is defined in config, that
     * @param $name
     * @return mixed
     */
    public function getPublicColumnName($name)
    {
        if(!isset($this->config['columns'][$name]))return $name;
        $col = $this->config['columns'][$name];
        return is_array($col) && isset($col['alias']) ? $col['alias'] : $name;
    }


    /**
     * Return config of a column
     * @param $column
     * @return array|null
     */
    public function getColumn($column)
    {
        $column = $this->getInternalColName($column);
        return isset($this->config['columns'][$column]) ? $this->config['columns'][$column] : null;
    }

    /**
     * Return internal column name, i.e. key in column config. alias name will be translated to internal names.
     * @param $column
     * @return mixed
     */
    public function getInternalColName($column)
    {
        return (isset($this->aliasMapping[$column])) ? $this->aliasMapping[$column] : $column;
    }

    /**
     * Return orderBy property from config.
     * @return null
     */
    public function getOrderBy()
    {
        return $this->getProperty('orderBy');
    }

    /**
     * Return true if any columns are defined in config.
     * @return bool
     */
    public function hasColumns()
    {
        $cols = $this->getColumns();
        return isset($cols) && is_array($cols) && count($cols) > 0;
    }

    /**
     * Return array of table names for joined columns.
     * @return array
     */
    public function getTableNamesFromJoins()
    {
        $ret = array();
        $joins = $this->getJoins();
        if (isset($joins)) {
            foreach ($joins as $join) {
                $ret[] = $join['table'];
            }
        }
        return $ret;
    }

    /**
     * Return a config property by key.
     * @param $key
     * @return null
     */
    protected function getProperty($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * Return true if config for given column is defined in config.
     * @param $columnName
     * @return bool
     */
    private function hasColumn($columnName)
    {
        return isset($this->config['columns'][$columnName]);
    }

    /**
     * Return name of column by "set" or "get" method.
     * @param $methodName
     * @return null|string
     */
    public function getColumnByMethod($methodName)
    {
        if (!$this->hasColumns()) return null;
        $col = $this->getFromMappingCache($methodName);
        if (!isset($col)) {
            $col = substr($methodName, 3);
            if ($this->hasColumn($col)) return $this->saveInMappingCache($methodName, $col);
            $col = lcfirst($col);
            if ($this->hasColumn($col)) return $this->saveInMappingCache($methodName, $col);
            $col = strtolower(preg_replace("/([A-Z])/s", "_$1", $col));
            if ($this->hasColumn($col)) return $this->saveInMappingCache($methodName, $col);
        }
        return $col;
    }

    /**
     * Save mapping between set and get methods and column names in internal cache for fast lookup.
     * @param $methodName
     * @param $col
     * @return mixed
     */
    private function saveInMappingCache($methodName, $col)
    {
        $t = $this->getTableName();
        if (!isset(self::$columnMappingCache[$t])) {
            self::$columnMappingCache[$t] = array();
        }
        self::$columnMappingCache[$t][$methodName] = $col;
        return $col;
    }

    /**
     * Return column name for given set or get method from mapping cache (fast lookup).
     * @param $methodName
     * @return null
     */
    private function getFromMappingCache($methodName)
    {
        $t = $this->getTableName();
        if (!isset(self::$columnMappingCache[$t])) {
            self::$columnMappingCache[$t] = array();
        }
        return isset(self::$columnMappingCache[$t][$methodName]) ? self::$columnMappingCache[$t][$methodName] : null;
    }

    /**
     * Returns true if user can write to given column. This is for the "save" and "setValues" methods of a LudoDBModel. You can
     * always call $this->setValue($name, $value) internally in your LudoDBModel classes.
     * @param $name
     * @return mixed
     */
    public function canWriteTo($name)
    {
        return $this->hasColumnAccess($name, 'w');
    }

    /**
     * Returns true if user can read value of given column. This is for the getValues and read methods of a LudoDBModel. You
     * will get access to column values internally using $this->getValue($columnName);
     * @param $name
     * @return mixed
     */
    public function canReadFrom($name)
    {
        return $this->hasColumnAccess($name, 'r');
    }

    /**
     * Cache of access to columns for fast lookup.
     * @var array
     */
    private $columnAccessCache = array();

    /**
     * Returns true if you have given access (read or write) to given column.
     * @param $name
     * @param $access
     * @return mixed
     */
    private function hasColumnAccess($name, $access)
    {
        $key = $name . "__" . $access;
        if (!isset($this->columnAccessCache[$key])) {
            if ($name === $this->getIdField()) {
                $this->columnAccessCache[$key] = true;
            } else {
                if ($this->isStaticColumn($name)) {
                    $this->columnAccessCache[$key] = $access === 'r';
                } else {
                    $column = $this->getColumn($name);
                    if (isset($column) && isset($column['access'])) {
                        $this->columnAccessCache[$key] = strstr($column['access'], $access) ? true : false;
                    } else {
                        $this->columnAccessCache[$key] = false;
                    }
                }
            }
        }
        return $this->columnAccessCache[$key];
    }

    /**
     * Returns true if given column is a static column, i.e. column not defined in database which should have a static value
     * defined in config.
     * @param $column
     * @return bool
     */
    public function isStaticColumn($column)
    {
        return isset($this->config['static'][$column]);
    }

    /**
     * Return value of static column.
     * @param $column
     * @return mixed
     */
    public function getStaticValue($column){
        return is_array($this->config['static'][$column]) ? $this->config['static'][$column]['value'] : $this->config['static'][$column];
    }

    /**
     * Return array of values for static columns. Name of column is the key in the returned array.
     * @return array
     */
    public function getStaticValues(){
        $ret = array();
        foreach($this->config['static'] as $key=>$value){
            $ret[$key] = $this->getStaticValue($key);
        }
        return $ret;
    }

    /**
     * Return static columns
     * @return array|null
     */
    public function getStaticColumns(){
        return $this->config['static'];
    }

    /**
     * Returns true if config has static columns.
     * @return bool
     */
    public function hasStaticColumns(){
        return !empty($this->config['static']);
    }

    /**
     * Returns true when LudoDBModel can be populated/constructed by this column.
     * @param $column
     * @return bool
     */
    public function canBePopulatedBy($column)
    {
        if ($column == $this->getIdField()) return true;
        $col = $this->getColumn($column);
        if (isset($this->config['constructBy']) && in_array($column, $this->config['constructBy'])) {
            return true;
        }
        return is_array($col) && isset($col['canConstructBy']) ? $col['canConstructBy'] : false;
    }

    /**
     * Returns references to other tables as array,
     * example array(
     *  array('table' => 'city', 'column' => 'zip'),
     *  array('table' => 'country', 'column' => 'id')
     * )
     * @return array
     */
    public function getTableReferences()
    {
        $ret = array();
        $cols = $this->getColumns();
        foreach ($cols as $col) {
            if (is_array($col) && isset($col['references'])) {
                $ret[] = array(
                    'table' => preg_replace("/^([^\(]+?)\(.*$/", "$1", $col['references']),
                    'column' => preg_replace("/^[^\(]+?\(([^\)]+)\).*$/", "$1", $col['references'])
                );
            }
        }
        return $ret;
    }

    /**
     * Return default value of a column.
     * @param $column
     * @return null
     */
    public function getDefaultValue($column)
    {
        return $this->getColumnProperty($column, 'default');
    }

    /**
     * Return default values of all columns.
     * @return array|null
     */
    public function getDefaultValues()
    {
        $ret = array();
        $cols = $this->getColumns();
        foreach ($cols as $col) {
            if (is_array($col) && isset($col['db']) && isset($col['default'])) {
                $ret[] = $col['default'];
            }
        }
        return count($ret) ? $ret : null;
    }

    /**
     * Returns ludoJS config for database columns and static columns.
     * @return array
     */
    public function getLudoJSConfig(){
        $ret = $this->getLudoJSOf($this->getColumns());
        $static = $this->getStaticColumns();
        if(isset($static))$ret = array_merge($ret, $this->getLudoJSOf($static));
        return $ret;
    }

    /**
     * Return LudoJS config of these columns
     * @param array $columns
     * @return array
     */
    private function getLudoJSOf($columns){
        $ret = array();
        foreach($columns as $name=>$def){
            if(isset($def['ludoJS'])){
                $ret[$name] = $def['ludoJS'];
                $ret[$name]['name'] = $name;
            }
        }
        return $ret;
    }

    /**
     * Cached columns requiring validation
     * @var array
     */
    private $validationColumns;

    /**
     * Return columns to validate
     * @return array
     */
    public function getColumnsToValidate(){
        if(!isset($this->validationColumns)){
            $this->validationColumns = array();
            $columns = $this->getColumns();
            foreach($columns as $name=>$def){
                if(is_array($def) && isset($def['validation'])){
                    $this->validationColumns[$name] = $def['validation'];
                }
            }
        }
        return $this->validationColumns;
    }
}
