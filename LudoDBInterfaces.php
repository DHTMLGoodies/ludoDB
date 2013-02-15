<?php
/**
 * LudoDB interfaces
 * User: Alf Magne
 * Date: 31.01.13

 */
interface LudoDBAdapter
{

    public function connect();

    public function query($sql, $params = array());

    public function one($sql, $params = array());

    public function countRows($sql, $params = array());

    public function getInsertId();

    public function nextRow($result);

    public function getValue($sql, $params = array());

    public function escapeString($string);
}

/**
 * Classes for request handlers has to implement the LudoDBService interface
 *
 * The class also needs to implement a static function called getValidServices
 * which returns an array of valid services, example array('read','save','delete');
 * Methods with these names also has to be implemented. "read", "save" and "delete"
 * are already implemented for LudoDBModel.
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

    public function validateServiceData($service, $arguments);

    /**
     * Return true to enable caching in LudoDBRequest handler for the read service.
     * When true a serialized version of LudoDBModel::read will
     * be stored in a caching table. When caching is enabled,
     * you should also implement clearCache() to clear cache in
     * case Data has been changed.
     * @return boolean
     */
    public function cacheEnabledFor($service);

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