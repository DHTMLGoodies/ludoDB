<?php
/**
 * Class for storage of values
 * User: Alf Magne
 * Date: 11.01.13
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * Registry for safe keeping of temporary values like database connection details etc.
 * @package LudoDB
 */
class LudoDBRegistry
{
    /**
     * Internal storage
     * @var array
     */
    private static $storage = array();

    /**
     * Store new value
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        self::$storage[$key] = $value;
    }

    /**
     * Get value
     * @param $key
     * @return null
     */
    public static function get($key)
    {
        if (self::isValid($key)) {
            return self::$storage[$key];
        }
        return null;
    }

    /**
     * Returns true when key is set in internal storage.
     * @param $key
     * @return bool
     */
    public static function isValid($key)
    {
        return isset(self::$storage[$key]);
    }
}
