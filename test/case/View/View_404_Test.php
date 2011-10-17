<?php
/**
 *  View_404_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_INSTALL_BASE . '/test/MockProject.php';

//{{{    Ethna_View_404_Test
/**
 *  Test Case For Ethna_View_404
 *
 *  @access public
 */
class Ethna_View_404_Test extends Ethna_UnitTestBase
{
    var $test_ctl;
    var $test_backend;
    var $view_404;

    function setUp()
    {
        $this->test_ctl = new Ethna_Controller();
        $this->test_backend = $this->test_ctl->getBackend();
        $this->view_404 = new Ethna_View_404($this->test_backend, '404', NULL);
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    function test_redirect_404()
    {
        $project = new Ethna_MockProject();
        $project->create();

        //   add mock action for redirect
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.404.php';
        $project->runCmd('add-action',
                         array(
                             '-s', $action_skel,
                             'return404',
                         )
        );

        $out = $project->runMain('return404');
        $this->assertPattern("/404 Not Found/", $out);

        $project->delete();
    }
}
