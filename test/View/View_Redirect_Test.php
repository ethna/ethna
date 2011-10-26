<?php
/**
 *  View_Redirect_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_INSTALL_BASE . '/test/MockProject.php';

//{{{    Ethna_View_Redirect_Test
/**
 *  Test Case For Ethna_View_Redirect
 *
 *  @access public
 */
class Ethna_View_Redirect_Test extends Ethna_UnitTestBase
{
    var $test_ctl;
    var $test_backend;
    var $view_redirect;

    function setUp()
    {
        $this->test_ctl = new Ethna_Controller();
        $this->test_backend = $this->test_ctl->getBackend();
        $this->view_redirect = new Ethna_View_Redirect($this->test_backend, 'redirect', NULL);
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    function test_preforward()
    {
        if (version_compare(PHP_VERSION, '5.0.0', '>')) {
            //    stop header output error for testing.
            @$this->view_redirect->preforward('http://www.aoimiyazaki.jp/');

            $headers_sent = headers_list();
            $this->assertNotA(
                array_search('Location: http://aoimiyazaki.jp', $headers_sent),
                false
            );
        }
    }

    function test_redirect_action()
    {
        $project = new Ethna_MockProject();
        $project->create();

        //   add mock action for redirect
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.redirect.php';
        $project->runCmd('add-action',
                         array(
                             '-s', $action_skel,
                             'redirect',
                         )
        );

        $out = $project->runMain('redirect');
        $this->assertEqual("", $out);

        if (version_compare(PHP_VERSION, '5.0.0', '>')) {
            $headers_sent = headers_list();
            $this->assertNotA(
                array_search('Location: http://www.ethnatest.example.com', $headers_sent),
                false
            );
        }

        $project->delete();
    }
}
