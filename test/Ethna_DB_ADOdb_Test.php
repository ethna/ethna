<?php
/**
 *  Ethna_DB_ADOdb_Test.php
 *
 *  @package Ethna
 *  @author halt feits <halt.feits@gmail.com>
 */

//error_reporting(E_ALL);
require_once 'Ethna/class/DB/Ethna_DB_ADOdb.php';

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

    function Ethna_DB_ADOdb_Test()
    {
        $this->db_path = dirname(__FILE__) . "/tmp/test.db";

        $test_dsn = "sqlite:///" . $this->db_path;
        $this->ctl =& new Ethna_Controller();
        $this->ctl->action_form = new Ethna_ActionForm($this->ctl);

        $this->backend =& $this->ctl->getBackend();
        $this->db = new Ethna_DB_ADOdb($this->ctl, $test_dsn, false);
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
        if (file_exists($this->db_path)) {
            unlink($this->db_path);
        }
    }

    function testMakeInstance()
    {
        $this->assertTrue(is_object($this->db), "this->db is not object");
        $this->assertEqual(get_class($this->db), "Ethna_DB_ADOdb", "this->db is not Ethna_DB_ADOdb");
    }

    function testConnect()
    {
        $this->assertTrue($this->db->connect(), "db connect failed");
        $this->assertTrue(file_exists($this->db_path), "db connect failed");
    }

}
?>
