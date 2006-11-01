<?php
/**
 *  Ethna_DB_ADOdb_Test.php
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 */

//error_reporting(E_ALL);

/**
 *  Ethna_DB_ADOdbクラスのテストケース
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 *  @access public
 */
class Ethna_DB_ADOdb_Test extends UnitTestCase
{
    /**
     * Ethna_Controller
     *
     * @var     Ethna_Controller
     * @access  protected
     */
    var $ctl;

    /**
     * Ethna_Backend
     *
     * @var     Ethna_Backend
     * @access  protected
     */
    var $backend;

    /**
     * ADOdb Object
     * @var     Ethna_DB_ADOdb
     * @access  private
     */
    var $db;

    /**
     * database path
     * @var     string
     * @access  private
     */
    var $db_path;

    /**
     * database source name
     * @var     string
     * @access  private
     */
    var $dsn;

    /**
     * database type
     * @var     double
     * @access  public
     */
    var $db_type;

    function Ethna_DB_ADOdb_Test()
    {
        //for sqlite
        $this->db_type = 'sqlite';
        $this->db_path = dirname(__FILE__) . "/tmp/test.db";
        $this->dsn = "sqlite:///" . $this->db_path;

        $this->ctl =& Ethna_Controller::getInstance();
        if (is_null($this->ctl)) {
            $this->ctl =& new Ethna_Controller();
            $this->ctl->action_form = new Ethna_ActionForm($this->ctl);
        }

        $this->backend =& $this->ctl->getBackend();

    }

    /**
     * setUp
     *
     * @access public
     * @param void
     */
    function setUp()
    {
    }

    function tearDown()
    {
        if ($this->db_type == 'sqlite') {
            if (file_exists($this->db_path)) {
                unlink($this->db_path);
            }
        }
    }

    function testRequire()
    {
        include_once 'Ethna/class/DB/Ethna_DB_ADOdb.php';
    }

    function testMakeInstance()
    {
        if ($this->db_type == 'sqlite') {
            $this->assertTrue(extension_loaded('sqlite'), "this php not installed sqlite");
        }

        $this->db = new Ethna_DB_ADOdb($this->ctl, $this->dsn, false);
        $this->assertTrue(is_object($this->db), "this->db is not object");
        $this->assertEqual(get_class($this->db), "Ethna_DB_ADOdb", "this->db is not Ethna_DB_ADOdb");
    }

    function testConnect()
    {
        $this->assertTrue($this->db->connect(), "db connect failed");
        $this->assertTrue(file_exists($this->db_path), "db connect failed");
    }

    function testCreateTable()
    {
        $sqls = array();
        $sqls[] = "CREATE TABLE test (id INTEGER NOT NULL PRIMARY KEY, string VARCHAR);";
        $sqls[] = "CREATE TABLE fordrop (id INTEGER NOT NULL PRIMARY KEY, string VARCHAR);";

        foreach($sqls as $sql) {
            $result = $this->db->execute($sql);
            $this->assertTrue($result, "query execute failed [$sql]");
        }
    }

    function testDeleteTable()
    {
        $sql = "DROP TABLE fordrop;";
        $result = $this->db->execute($sql);
        $this->assertTrue($result, "query execute failed [$sql]");
    }

    function testInsert()
    {
        $sqls = array();
        $sqls[] = "INSERT INTO test (string) VALUES ('test_string');";
        $sqls[] = "INSERT INTO test (string) VALUES ('1');";
        $sqls[] = array(
            'query' => 'INSERT INTO test (string) VALUES (?);',
            'ps' => array('test_data'),
        );

        foreach ($sqls as $sql) {

            if (is_string($sql)) {
                $result = $this->db->execute($sql);
            } else if (is_array($sql)) {
                $result = $this->db->execute($sql['query'], $sql['ps']);
            } else {
                $this->fail("invalid test data");
            }

            $this->assertTrue($result, "query execute failed [$sql]");
        }
    }

    function testAutoExecute()
    {
        $result = $this->db->autoExecute('test', array('string' => __LINE__), 'INSERT');
        $this->assertTrue($result, "autoexecute failed");

        $result = $this->db->autoExecute('test', array('string' => 'testAutoExecute'), 'INSERT');
        $this->assertTrue($result, "autoexecute failed");

        $result = $this->db->autoExecute('test', array('string' => 'edit_testAutoExecute_edit'), 'UPDATE', "string = 'testAutoExecute'");
        $this->assertTrue($result, "autoexecute failed");
    }

    function testExecute()
    {
        $sqls = array();
        $sqls[] = "CREATE TABLE test_execute (id INTEGER NOT NULL PRIMARY KEY, string VARCHAR);";

        foreach($sqls as $sql) {
            $result = $this->db->execute($sql);
            $this->assertTrue($result, "query execute failed [$sql]");
        }
    }

}
?>
