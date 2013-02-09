<?php
/**
 * LudoDB PHPUnit base class for tests
 * User: borrow
 * Date: 19.12.12

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

        $util = new LudoDBUtility();
        $tables = array('TestNode','LeafNode','TestTable','Person','Phone','City');
        $util->dropAndCreate($tables);

    }

    private $dbInstance;
    protected function getDb(){
        if(!isset($this->dbInstance)){
            $this->dbInstance = LudoDB::getInstance();
        }
        return $this->dbInstance;
    }

    private function connect()
    {
        LudoDB::setHost('127.0.0.1');
        LudoDB::setUser('root');
        LudoDB::setPassword('administrator');
        LudoDB::setDb('PHPUnit');

        $this->connected = true;
    }

    protected function clearTable()
    {
        $db = LudoDB::getInstance();
        $db->query("delete from TestTable");
    }

    private function clearLog()
    {
        if (!self::$logCleared) {
            self::$logCleared = true;
            $fh = fopen("test-log.txt", "w");
            fwrite($fh, "\n");
            fclose($fh);

            $fh = fopen("sql.txt", "w");
            fwrite($fh, "\n");
            fclose($fh);
        }
    }

    public function log($sql)
    {
        if(is_array($sql))$sql = json_encode($sql);
        $fh = fopen("test-log.txt", "a+");
        fwrite($fh, $sql . "\n");
        fclose($fh);
    }

}
