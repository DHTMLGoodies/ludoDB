<?php
/**
 * User: Alf Magne Kalleland
 * Date: 17.04.13
 * Time: 21:37
 * @package LudoDBS
 */
/**
 * Singleton class used to validate data before they are inserted into the database
 * @package LudoDB
 */
class LudoDBValidator
{

    /**
     * Required validator property
     * Example:
     *
     * <code>
     * firstName => array(
     *  "db" => "varchar(64)",
     *  "validation" => array(
     *      "required" => true
     *  )
     * )
     * </code>
     */
    const REQUIRED = 'required';

    /**
     * Min length validation, i.e. length of string
     * Example:
     *
     * <code>
     * firstName => array(
     *  "db" => "varchar(64)",
     *  "validation" => array(
     *      "minLength" => 5
     *  )
     * )
     * </code>
     */
    const MIN_LENGTH = 'minLength';

    /**
     * Max length validation, i.e. length of string
     * Example:
     *
     * <code>
     * firstName => array(
     *  "db" => "varchar(64)",
     *  "validation" => array(
     *      "maxLength" => 5
     *  )
     * )
     * </code>
     */
    const MAX_LENGTH = 'maxLength';
    /**
     * Minimum value validation, i.e. numeric value
     * Example:
     *
     * <code>
     * firstName => array(
     *  "db" => "varchar(64)",
     *  "validation" => array(
     *      "minValue" => 5,
     *      "maxValue" => 10
     *  )
     * )
     * </code>
     */
    const MIN_VALUE = 'minValue';
    /**
     * Maximum value validation, i.e. numeric value
     * Example:
     *
     * <code>
     * firstName => array(
     *  "db" => "varchar(64)",
     *  "validation" => array(
     *      "minValue" => 5,
     *      "maxValue" => 10
     *  )
     * )
     * </code>
     */
    const MAX_VALUE = 'maxValue';
    /**
     * Regular expression value validation, i.e. numeric value
     * Example:
     *
     * <code>
     * firstName => array(
     *  "db" => "varchar(64)",
     *  "validation" => array(
     *      "regex" => "^[0-9]+$"
     *  )
     * )
     * </code>
     */
    const REGEX = 'regex';

    /**
     * Singleton LudoDBValidator instance.
     * @var LudoDBValidator
     */
    private static $instance;

    /**
     * Return singleton instance.
     * @return LudoDBValidator
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new LudoDBValidator();
        }
        return self::$instance;
    }

    /**
     * Validate data for "save statement"
     * @param LudoDBModel $model
     * @throws LudoDBInvalidModelDataException
     */
    public function validateSave($model)
    {
        $data = $model->getUncommitted();
        $validationDef = $model->configParser()->getColumnsToValidate();
        if (empty($validationDef)) return;

        foreach ($validationDef as $column => $def) {
            if (isset($def['required']) && empty($data[$column])) {
                throw new LudoDBInvalidModelDataException($column . " is required");
            }

            if (isset($data[$column])) {
                $this->validateColumn($column, $data[$column], $def);
            }
        }
    }

    /**
     * Validate data for "update" statement
     * @param LudoDBModel $model
     */
    public function validateUpdate($model)
    {
        $data = $model->getUncommitted();
        $validationDef = $model->configParser()->getColumnsToValidate();
        if (empty($validationDef)) return;

        foreach ($validationDef as $column => $def) {
            if (isset($data[$column])) {
                $this->validateColumn($column, $data[$column], $def);
            }
        }
    }

    /**
     * Run validation on a column
     * @param $column
     * @param $dataValue
     * @param $def
     * @throws LudoDBInvalidModelDataException
     */
    private function validateColumn($column, $dataValue, $def)
    {
        foreach ($def as $key => $value) {
            switch ($key) {
                case self::MIN_LENGTH:
                    if (strlen($dataValue) < $value) {
                        throw new LudoDBInvalidModelDataException($column . " is too short. Required min length : ". $value . " but was ". strlen($dataValue));
                    }
                    break;
                case self::MAX_LENGTH:
                    if (strlen($dataValue) > $value) {
                        throw new LudoDBInvalidModelDataException($column . " is long short. Required max length : ". $value . " but was ". strlen($dataValue));
                    }
                    break;
                case self::REGEX:
                    if(!preg_match($value, $dataValue)){
                        throw new LudoDBInvalidModelDataException($column . " does not match expected regex : ". $value);
                    }
                    break;
                case self::MIN_VALUE:
                    if(floatval($dataValue) < $value){
                        throw new LudoDBInvalidModelDataException($column . " is to low. Expected min value: : ". $value);
                    }
                    break;
                case self::MAX_VALUE:
                    if(floatval($dataValue) > $value){
                        throw new LudoDBInvalidModelDataException($column . " is to low. Expected min value: : ". $value);
                    }
                    break;
                case self::REQUIRED:
                    if(empty($dataValue)){
                        throw new LudoDBInvalidModelDataException($column . " is required");
                    }
                    break;
                default:

            }
        }
    }
}
