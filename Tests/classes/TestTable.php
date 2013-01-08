<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 04.11.12
 * Time: 03:31
 * To change this template use File | Settings | File Templates.
 */
class TestTable extends LudoDbTable
{
    protected $config = array(
        'idField' => 'id',
        'table' => 'TestTable',
        'columns' => array(
            'id' => 'int auto_increment not null primary key',
            'firstname' => 'varchar(32)',
            'lastname' => 'varchar(32)',
            'address' => 'varchar(64)'
        ),
    );

    public function setFirstName($value)
    {
        $this->setValue('firstname', $value);
    }

    public function getFirstname()
    {
        return $this->getValue('firstname');
    }

    public function setLastname($value)
    {
        $this->setValue('lastname', $value);
    }

    public function getLastname()
    {
        return $this->getValue('lastname');
    }
}
