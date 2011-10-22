<?php
/**
 *  Session_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 *  @TODO       Create session mock and fix this test
 */

require_once ETHNA_INSTALL_BASE . '/test/MockProject.php';

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

        $this->local_session = new Ethna_Session_Mock($this->ctl, "ETHNA_TEST");
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
        $this->local_session->start(0);
        $this->assertFalse($this->local_session->isAnonymous());
        $this->assertTrue($this->local_session->isStart());
    }
}


class Ethna_Session_Mock
    extends Ethna_Session
{

    public function start($lifetime = 0, $anonymous = false)
    {
        if ($this->session_start) {
            // we need this?
            $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['__anonymous__'] = $anonymous;
            return true;
        }

        if (is_null($lifetime)) {
            ini_set('session.use_cookies', 0);
        } else {
            ini_set('session.use_cookies', 1);
        }

        session_set_cookie_params($lifetime);
        session_id(Ethna_Util::getRandom());
        // do not start test as cli test
        //session_start();

        $_SESSION['REMOTE_ADDR'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']: false;
        $_SESSION['__anonymous__'] = $anonymous;
        $this->anonymous = $anonymous;
        $this->session_start = true;

        $this->logger->log(LOG_INFO, 'Session started.');

        return true;
    }

    public function destroy()
    {
        if (!$this->session_start) {
            return true;
        }

        //session_destroy();
        $this->session_start = false;
        //setcookie($this->session_name, "", 0, "/");

        return true;
    }
}
