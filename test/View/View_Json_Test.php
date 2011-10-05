<?php
/**
 *  View_Json_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

//{{{    Ethna_View_Json_Test
/**
 *  Test Case For Ethna_View_Json
 *
 *  @access public
 */
class Ethna_View_Json_Test extends Ethna_UnitTestBase
{
    var $test_ctl;
    var $test_backend;
    var $view_json;

    function setUp()
    {
        $this->test_ctl = new Ethna_Controller();
        $this->test_backend = $this->test_ctl->getBackend();
        $this->view_json = new Ethna_View_Json($this->test_backend, 'json', NULL);
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    function test_preforward_utf8()
    {
        $param = array("a", "あいうえ");

        //    stop header output for testing.
        ob_start();
        //    stop header output error for testing.
        @$this->view_json->preforward($param);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEqual($result, '["a","\u3042\u3044\u3046\u3048"]');
    }

    function test_preforward_non_utf8()
    {
        $this->test_ctl->setClientEncoding('EUC-JP');

        $param = array("a", "あいうえ");
        mb_convert_variables('EUC-JP', 'UTF-8', $param);

        ob_start();
        //    stop header output for testing.
        @$this->view_json->preforward($param);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEqual($result, '["a","\u3042\u3044\u3046\u3048"]');

        $this->test_ctl->setClientEncoding('UTF-8');
    }

    function test_json_action()
    {
        $project = new Ethna_MockProject();
        $project->create();

        //   add mock action for redirect
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.json.php';
        $project->runCmd('add-action',
                         array(
                             '-s', $action_skel,
                             'json',
                         )
        );

        $out = $project->runMain('json');
        $this->assertEqual('["a","b"]', $out);

        $project->delete();
    }
}
