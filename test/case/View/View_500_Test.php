<?php
/**
 *  View_500_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_INSTALL_BASE . '/test/MockProject.php';

//{{{    Ethna_View_500_Test
/**
 *  Test Case For Ethna_View_500
 *
 *  @access public
 */
class Ethna_View_500_Test extends Ethna_UnitTestBase
{
    var $test_ctl;
    var $test_backend;
    var $view_500;

    function setUp()
    {
        $this->test_ctl = new Ethna_Controller();
        $this->test_backend = $this->test_ctl->getBackend();
        $this->view_500 = new Ethna_View_500($this->test_backend, '500', NULL);
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    function test_redirect_500()
    {
        $project = new Ethna_MockProject();
        $project->create();

        //   add mock action for redirect
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.500.php';
        $project->runCmd('add-action',
                         array(
                             '-s', $action_skel,
                             'return500',
                         )
        );

        $out = $project->runMain('return500');
        $this->assertPattern("/500 Internal Server Error/", $out);

        $project->delete();
    }
}
