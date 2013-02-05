<?php
/**
 * Class for storage of values
 * User: Alf Magne
 * Date: 11.01.13
 * Time: 12:20
 */
class LudoDBRegistry
{
    private static $storage = array();

    public static function set($key, $value)
    {
        self::$storage[$key] = $value;
    }

    public static function get($key)
    {
        if (self::isValid($key)) {
            return self::$storage[$key];
        }
        return null;
    }

    public static function isValid($key)
    {
        return isset(self::$storage[$key]);
    }
}
