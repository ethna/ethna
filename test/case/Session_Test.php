<?php
/**
 *  Session_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_INSTALL_BASE . '/test/lib/MockProject.php';

//{{{  Ethna_Session_Test
/**
 *  Test Case For Ethna_Session
 *
 *  @access public
 */
class Ethna_Session_Test extends Ethna_UnitTestBase
{
    var $local_session;

    // mock project
    var $project;

    function setUp()
    {
        $this->project = new Ethna_MockProject();
        $this->project->create();

        $this->local_session = new Ethna_Session($this->ctl, "ETHNA_TEST");
    }

    function tearDown()
    {
        $this->local_session->destroy();
        $this->local_session = NULL;

        $this->project->delete();
        unset($GLOBALS['_Ethna_controller']);
    }

    function test_isAnonymous()
    {
        //   suppress header already sent error.
        $this->local_session->start(0, true);
        //$this->assertTrue($this->local_session->isAnonymous());
    }
}

