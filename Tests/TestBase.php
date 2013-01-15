<?php
/**
 * Created by JetBrains PhpStorm.
 * User: borrow
 * Date: 19.12.12
 * Time: 15:37
 * To change this template use File | Settings | File Templates.
 */
class TestBase extends PHPUnit_Framework_TestCase
{
    private $connected = false;
    private static $logCleared = false;

    public function setUp()
    {
        ini_set('display_errors', 'on');
        date_default_timezone_set('Europe/Berlin');
        $this->clearLog();
        if (!$this->connected) $this->connect();
        $this->dropTable();
        $tbl = new TestTable();
        $tbl->createTable();

        $p = new Person();
        $p->drop();
        $p->createTable();

        $p = new Phone();
        $p->drop();
        $p->createTable();
    }

    private function connect()
    {
        LudoDB::setHost('localhost');
        LudoDB::setUser('root');
        LudoDB::setPassword('administrator');
        LudoDB::setDb('PHPUnit');

        $this->connected = true;
    }

    protected function clearTable()
    {
        $db = new LudoDB();
        $db->query("delete from TestTable");
    }

    protected function dropTable()
    {
        $db = new LudoDB();
        $t = new TestTable();
        $t->drop();
    }

    private function clearLog()
    {
        if (!self::$logCleared) {
            self::$logCleared = true;
            $fh = fopen("sql.txt", "w");
            fwrite($fh, "\n");
            fclose($fh);
        }
    }

    public function log($sql)
    {
        if(is_array($sql))$sql = json_encode($sql);
        $fh = fopen("sql.txt", "a+");
        fwrite($fh, $sql . "\n");
        fclose($fh);
    }

}
