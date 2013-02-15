<?php
/**
 * By registering LudoDBService classes, you may get an overview of all the resources and
 * services you have available.
 * User: Alf Magne Kalleland
 * Date: 12.02.13
 * Time: 23:14
 */
class LudoDBServiceRegistry
{
    private static $registeredServices = array();

    /**
     * Register service resource. If you need access to all available services, you can call
     * LudoDBServiceRegistry::getAll(). Argument is name of a LudoDBService class.
     * @param String $resource
     */
    public static function register($resource)
    {
        if (class_exists($resource)) {
            $r = new ReflectionClass($resource);
            if ($r->implementsInterface("LudoDBService")) {
                try {
                    self::$registeredServices[$resource] = $r->getMethod("getValidServices")->invoke(new $resource);
                } catch (Exception $e) {
                    self::$registeredServices[$resource] = array('NA');
                }
            }
        }
    }

    /**
     * Return array of all registered service resources with service names.
     * @return array
     */
    public static function getAll()
    {
        ksort(self::$registeredServices);
        return self::$registeredServices;
    }
}
