<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 17.04.13
 * Time: 21:09
 */
class PersonWithValidation extends LudoDBModel
{
    protected $config = array(
        "table" => "ModelWithValidation",
        "columns" => array(
            "id" => "int auto_increment not null primary key",
            "firstname" => array(
                "db" => "varchar(64)",
                "validation" => array(
                    "required" => true
                )
            ),
            "lastname" => array(
                "db" => "varchar(64)"
            ),
            "mail" => array(
                "db" => "varchar(64)",
                "validation" => array(
                    "required" => true
                )
            ),
            "nameWithMinLength" => array(
                "db" => "varchar(64)",
                "validation" => array(
                    "minLength" => 5
                )
            ),
            "nameWithMaxLength" => array(
                "db" => "varchar(5)",
                "validation" => array(
                    "maxLength" => 5
                )
            ),
            "nameWithNumericRegex" => array(
                "db" => "varchar(32)",
                "validation" => array(
                    "regex" => "/^[0-9]+?$/"
                )
            ),
            "nameWithMinVal" => array(
                "db" => "int",
                "validation" => array(
                    "minValue" => 20
                )
            ),

            "nameWithMaxVal" => array(
                "db" => "int",
                "validation" => array(
                    "maxValue" => 20
                )
            ),
            "nameWithMinAndMaxVal" => array(
                "db" => "int",
                "validation" => array(
                    "minValue" => 20,
                    "maxValue" => 50
                )
            )
        )
    );

    public function setFirstname($name){
        $this->setValue('firstname', $name);
    }

    public function setLastname($name){
        $this->setValue('lastname', $name);
    }

    public function setMail($address){
        $this->setValue('mail', $address);
    }

    public function setNameWithMinLength($value){
        $this->setValue("nameWithMinLength", $value);
    }

    public function setNameWithMaxLength($value){
        $this->setValue("nameWithMaxLength", $value);
    }

    public function setNameWithNumericRegex($value){
        $this->setValue("nameWithNumericRegex", $value);
    }

    public function setNameWithMinVal($value){
        $this->setValue("nameWithMinVal", $value);
    }
    public function setNameWithMaxVal($value){
        $this->setValue("nameWithMaxVal", $value);
    }
    public function setNameWithMinAndMaxVal($value){
        $this->setValue("nameWithMinAndMaxVal", $value);
    }
}
