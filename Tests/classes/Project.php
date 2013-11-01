<?php
/**
 *
 * User: Alf Magne
 * Date: 16.10.13
 * Time: 13:18
 */
class Project extends LudoDBModel
{
    protected $config = array(
        'idField' => 'id',
        'table' => 'project',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'title' => array(
                'db' => 'varchar(128)',
                'access' => 'rw'
            ),
            'description' => array(
                'db' => 'varchar(2000)',
                'access' => 'rw'
            )
        ),
        'data' => array(
            array('id' => '1', 'title' => 'Internal project','description' => 'My internal project'),
            array('id' => '2', 'title' => 'Project ACME development','description' => 'Software development for the service company ACME'),
            array('id' => '3', 'title' => 'Project other development','description' => 'Software development for misc clients'),
            array('id' => '4', 'title' => 'Android training','description' => 'Internal Android development training'),
            array('id' => '5', 'title' => 'Main product - design development','description' => 'Develop graphic design elements for our main product'),
            array('id' => '6', 'title' => 'Web site acme-software.com','description' => 'Web development of web site acme-software.com'),
            array('id' => '7', 'title' => 'iOS training project','description' => 'Internal training for iOS development'),
            array('id' => '8', 'title' => 'Acme CRM','description' => 'Logging of development work.'),
        )

    );

    public function setTitle($title){
        $this->setValue("title", $title);
    }

    public function getTitle(){
        return $this->getValue("title");
    }
}
