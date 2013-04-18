<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 13.04.13
 * Time: 15:59
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */
/**
 * LudoJS service class. This class outputs LudoJS config objects for LudoDB instances
 *
 * Example where LudoJS config for LudoDBModel Person is returned with values of Person with id equals 1:
 *
 * <code>
 * $handler = new LudoDBRequestHandler();
 * echo $handler->handle('LudoJS/Person/1/form');
 * </code>
 *
 * This will return the config as a JSON string.
 *
 * @package LudoDB
 * @author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */

class LudoJS implements LudoDBService
{
    /**
     * Reference to the LudoDBObject to handle
     * @var LudoDBObject
     */
    private $resource;

    /**
     * Construct new instance
     */
    public function __construct()
    {
        $this->arguments = func_get_args();
    }

    /**
     * Return "form" as only valid service
     * @return array
     */
    public function getValidServices()
    {
        return array("form");
    }

    /**
     * Accepted number of arguments is 1 or 2 (second argument is optional "id" of resource)
     * @param String $service
     * @param Array $arguments
     * @return bool
     */
    public function validateArguments($service, $arguments)
    {
        if (count($arguments) === 2) return is_numeric($arguments[1]);
        return count($arguments) > 0;
    }

    /**
     * No data is allowed to this service
     * @param string $service
     * @param array $data
     * @return bool
     */
    public function validateServiceData($service, $data)
    {
        return empty($data);
    }

    /**
     * No caching
     * @param string $service
     * @return bool
     */
    public function shouldCache($service)
    {
        return false;
    }

    /**
     * Returns empty string as on success message for service
     * @param String $service
     * @return String
     */
    public function getOnSuccessMessageFor($service)
    {
        return "";
    }

    /**
     * "form" service
     * @return array
     */
    public function form()
    {
        $this->resource = $this->getModelResource();

        $children = $this->resource->configParser()->getLudoJSConfig();
        $children = $this->setMissingProperties($children);
        $children = $this->setChildValues($children);
        $children = $this->createDataSources($children);
        $children = $this->addValidation($children);
        $children = array_values($children);
        $children = $this->getChildrenInRightOrder($children);

        return array(
            'children' => $children,
            'form' => array(
                'resource' => $this->arguments[0]

            )
        );

    }

    /**
     * Update child array with default properties when not specified in config, example "label"
     * @param $children
     * @return mixed
     */
    private function setMissingProperties($children)
    {
        foreach ($children as $col => &$def) {
            if (!isset($child['label'])) {
                $def['label'] = isset($def['label']) ? $def['label'] : ucfirst($col);
            }
        }
        return $children;
    }

    /**
     * Create dataSource objects for children.
     * @param $children
     * @return mixed
     */
    private function createDataSources($children)
    {
        foreach ($children as &$def) {
            if (isset($def['dataSource'])) {
                $def['dataSource'] = $this->getDataSourceConfig($def['dataSource']);
            }
        }
        return $children;
    }

    /**
     * Return data source config with values for columns.
     * @param $source
     * @return array
     */
    private function getDataSourceConfig($source)
    {
        if (!is_array($source)) {
            $source = array('name' => $source);
        }
        $cl = $this->getReflectionClass($source['name']);
        if (isset($source['args'])) {
            $args = is_array($source['args']) ? $source['args'] : array($source['args']);
            $resource = $cl->newInstanceArgs($args);
        } else {
            $resource = $cl->newInstance();
        }
        return array(
            'data' => $resource->getValues()
        );
    }

    /**
     * Return childrne in correct order according to "order" attribute
     * @param array $children
     * @return array
     */
    private function getChildrenInRightOrder($children)
    {
        for ($i = 0, $count = count($children); $i < $count; $i++) {
            if (!isset($children[$i]['order'])) {
                $children[$i]['order'] = $i + 100;
            }
        }
        usort($children, function ($a, $b) {
            return $a['order'] > $b['order'] ? 1 : -1;
        });

        foreach ($children as & $child) {
            unset($child['order']);
        }
        return $children;
    }

    /**
     * Add "value" properties to children.
     * @param $children
     * @return mixed
     */
    private function setChildValues($children)
    {
        if (isset($this->arguments[1])) {
            $values = $this->resource->getValues();
            foreach ($values as $key => $value) {
                if (isset($children[$key])) {
                    $children[$key]['value'] = $value;
                }
            }
        }
        return $children;
    }

    /**
     * Add validation properties to LudoJS column
     * @param $children
     * @return mixed
     */
    private function addValidation($children)
    {
        $validations = $this->resource->configParser()->getColumnsToValidate();
        foreach ($validations as $col => $validation) {
            foreach ($validation as $key => $value) {
                switch ($key) {
                    case "regex":
                        $tokens = explode("/", $value);
                        $flag = array_pop($tokens);
                        if($this->isRegexFlag($flag)){
                            $flag= str_replace("s", "g", $flag);
                        }
                        $tokens[] = $flag;
                        $children[$col][$key] = implode("/",$tokens);
                        break;
                    default:
                        $children[$col][$key] = $value;

                }
            }
        }
        return $children;
    }

    /**
     * Returns true if given string matches pattern for a regex flag/modifiers
     * @param $token
     * @return int
     */
    private function isRegexFlag($token){
        return preg_match("/^[si]+?$/", $token);
    }

    /**
     * Return model resource to handle
     * @return LudoDBObject
     * @throws LudoDBClassNotFoundException
     */
    private function getModelResource()
    {
        if (!class_exists($this->arguments[0])) {
            throw new LudoDBClassNotFoundException("Class " . $this->arguments[0] . " does not exists.");
        }
        $resource = $this->getReflectionClass($this->arguments[0]);

        if (isset($this->arguments[1])) {
            return $resource->newInstanceArgs(array($this->arguments[1]));
        }
        return $resource->newInstance();
    }

    /**
     * Use Reflection to get instance of resource class
     * @param $className
     * @return ReflectionClass
     * @throws LudoDBClassNotFoundException
     */
    private function getReflectionClass($className)
    {
        $cl = new ReflectionClass($className);
        if (!$cl->implementsInterface('LudoDBService')) {
            throw new LudoDBClassNotFoundException($className . " is not an instance of LudoDBService");
        }
        return $cl;
    }
}
