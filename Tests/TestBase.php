<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 19.12.12
 * Time: 15:37
 * To change this template use File | Settings | File Templates.
 */
class TestBase  extends PHPUnit_Framework_TestCase
{
    private $connected = false;
    private $dbUser = 'root';
    private $dbPassword = 'administrator';

    public function setUp(){
        if(!$this->connected)$this->connect();
        $this->dropTable();
        $tbl = new TestTable();
        $tbl->createTable();
    }

    private function connect(){
        $res = mysql_connect("localhost", $this->dbUser, $this->dbPassword);
        mysql_select_db('PHPUnit', $res);

        $this->connected = true;
    }

    protected function clearTable(){
        mysql_query("delete from TestTable");
    }
    protected function dropTable(){
        mysql_query("drop table TestTable");
    }
    public function log($sql){
        $fh = fopen("sql.txt","a+");
        fwrite($fh, $sql."\n");
        fclose($fh);
    }

}
