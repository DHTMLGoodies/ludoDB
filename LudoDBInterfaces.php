<?php
/**
 * LudoDB interfaces
 * User: Alf Magne
 * Date: 31.01.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */

/**
 * Interface for LudoDB database connection adapters
 * @package LudoDB
 */
interface LudoDBAdapter
{
    /**
     * Connect to database
     * @return mixed
     */
    public function connect();

    /**
     * Execute a query and return resource
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function query($sql, $params = array());

    /**
     * Execute query and return first row.
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function one($sql, $params = array());

    /**
     * Return number of rows for given SQL with given params (for prepared statements).
     *
     * Example
     *
     * <code>
     * $count = LudoDB::getInstance()->countRows("select * from city where country=?", array("Norway"));
     * </code>
     *
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function countRows($sql, $params = array());

    /**
     * Return id of last inserted record
     * @return string
     */
    public function getInsertId();

    /**
     * Return next row in result set.
     * @param $result
     * @return array
     */
    public function nextRow($result);

    /**
     * Return value of first column in first row of query.
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function getValue($sql, $params = array());

    /**
     * Escape string to be inserted into the database
     * @param $string
     * @return mixed
     */
    public function escapeString($string);

    /**
     * Return table definition, column names and column types for a table.
     * @param String $tableName
     * @return array
     */
    public function getTableDefinition($tableName);
}

/**
 * Classes for request handlers has to implement the LudoDBService interface
 *
 * The class also needs to implement a static function called getValidServices
 * which returns an array of valid services, example array('read','save','delete');
 * Methods with these names also has to be implemented. "read", "save" and "delete"
 * are already implemented for LudoDBModel.
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
interface LudoDBService
{
    /**
     * Returns true is passed arguments are acceptable for the constructor.
     * @param String $service
     * @param Array $arguments
     * @return bool
     */
    public function validateArguments($service, $arguments);

    /**
     * Validate data sent to service method
     * @param string $service
     * @param array $data
     * @return bool
     */
    public function validateServiceData($service, $data);

    /**
     * Return true to enable caching in LudoDBRequest handler for the read service.
     * When true a serialized version of LudoDBModel::read will
     * be stored in a caching table. When caching is enabled,
     * you should also implement clearCache() to clear cache in
     * case Data has been changed.
     * @param string $service
     * @return boolean
     */
    public function shouldCache($service);

    /**
     * Return array with names of valid services
     * @return array
     */
    public function getValidServices();

    /**
     * Return on success message for given service
     * @param String $service
     * @return String
     */
    public function getOnSuccessMessageFor($service);



}

interface LudoDBAuthenticator
{
    public function authenticate($resource, $service, $arguments, $data);
}