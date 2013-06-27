<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 13.04.13
 * Time: 16:37
 */
class TLudoJSPerson extends LudoDBModel implements LudoDBService
{
    protected $config = array(
        'table' => 'LudoJSPerson',
        'columns' => array(
            'id' => array(
                'db' => 'int auto_increment not null primary key',
                'ludoJS' => array(
                    'type' => 'form.Hidden'
                )
            ),
            'lastname' => array(
                'db' => 'varchar(32)',
                'ludoJS' => array(
                    'type' => 'form.Text',
                    'order' => 2
                ),
                "validation" => array(
                    "required" => true
                ),
                "access" => "rw"
            ),
            'firstname' => array(
                'db' => 'varchar(32)',
                'ludoJS' => array(
                    'type' => 'form.Text',
                    'order' => 1
                ),
                "access" => "rw",
                "validation" => array(
                    'required' => true
                )
            ),
            "country" => array(
                "db" => "int",
                "references" => "LudoJSCountry(id)",
                "ludoJS" => array(
                    'valueKey' => 'id',
                    'textKey' => 'name',
                    'type' => 'form.Select',
                    'order' => '10',
                    'dataSource' => 'TLudoJSCountries'
                ),
                "access" => "rw"
            ),
            "address" => array(
                "db" => "varchar(4000)",
                "ludoJS" => array(
                    'type' => 'form.Textarea',
                    'order' => 3,
                    'layout' => array(
                        'weight' => 1
                    )
                ),
                "access" => "rw"
            ),
            "zip" => array(
                "db" => "varchar(10)",
                "access" => "rw",
                "ludoJS" => array(
                    "label" => "Zip code",
                    "type" => "form.Text",
                    "order" => 4
                )
            ),
            "city" => array(
                "db" => "varchar(10)",
                "access" => "rw",
                "ludoJS" => array(
                    "type" => "form.Text",
                    "order" => 5
                )
            )
        ),
        "static" => array(
            "type" => array(
                "value" => "person",
                "ludoJS" => array(
                    'type' => 'form.Hidden'
                ),
                "access" => "rw"
            )
        ),
        "data" => array(
            array("firstname" => "John", "lastname" => "Johnson", "country" => 131, "address" => "Main street 99")
        )
    );

    public function validateArguments($service, $arguments){
        return true;
    }

    public function validateServiceData($service, $data){
        return true;
    }

}
