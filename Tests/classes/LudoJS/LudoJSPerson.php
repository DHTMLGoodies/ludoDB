<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 13.04.13
 * Time: 16:37
 */
class LudoJSPerson extends LudoDBModel implements LudoDBService
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
                "access" => "rw"
            ),
            'firstname' => array(
                'db' => 'varchar(32)',
                'ludoJS' => array(
                    'type' => 'form.Text',
                    'order' => 1
                ),
                "access" => "rw"
            ),
            "country" => array(
                "db" => "int",
                "references" => "LudoJSCountry(id)",
                "ludoJS" => array(
                    'type' => 'form.Select',
                    'order' => '4',
                    'dataSource' => 'LudoJSCountries'
                )
            ),
            "address" => array(
                "db" => "varchar(4000)",
                "ludoJS" => array(
                    'type' => 'form.Textarea',
                    'order' => 3
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
