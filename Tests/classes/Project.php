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
            )
        ),
        'data' => array(
            array('id' => '1', 'title' => 'Internal project'),
            array('id' => '2', 'title' => 'Project ACME development'),
            array('id' => '3', 'title' => 'Project other development')
        )

    );

    public function setTitle($title){
        $this->setValue("title", $title);
    }

    public function getTitle(){
        return $this->getValue("title");
    }
}
