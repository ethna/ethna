<?php
// vim: foldmethod=marker
/**
 *  Plugin_Handle_I18n_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/test/MockProject.php';

//{{{  Ethna_Plugin_Handle_I18n_Test
/**
 *  Test Case For Ethna_Plugin_Handle_I18n_Test 
 *
 *  @access public
 */
class Ethna_Plugin_Handle_I18n_Test extends Ethna_UnitTestBase 
{
    var $proj;
    var $mock_ctl;
    var $msg_file;
    var $i18n;

    function setUp()
    {
        $this->proj = new Ethna_MockProject();
        $r = $this->proj->create();
        if (Ethna::isError($r)) {
            $this->fail($r->getMessage());    
        }
        $this->mock_ctl = $this->proj->getController();
        $locale_dir = $this->mock_ctl->getDirectory('locale');
        $locale = $this->mock_ctl->getLocale();
        $this->msg_file = $locale_dir . '/'
                        . $locale . '/'
                        . 'LC_MESSAGES/'
                        . "$locale.ini";
        $this->i18n = $this->mock_ctl->getI18N();
    }

    function tearDown()
    {
        $this->proj->delete();
    }

    function test_Action()
    {
        $skel = ETHNA_TEST_SKELDIR . 'skel.action.i18ntest.php';   
        $r = $this->proj->runCmd('add-action',
                          array(
                              '-s', $skel,
                              'i18n', 
                          ) 
             );
        if (Ethna::isError($r)) {
            $this->fail($r->getMessage());
        }
        $this->run_i18n_cmd();
        $catalog = $this->i18n->parseEthnaMsgCatalog($this->msg_file);

        //  assert ActionForm definition.
        $this->assertTrue(isset($catalog['name_i18n']));
        $this->assertTrue(isset($catalog['required_error_i18n']));
        $this->assertTrue(isset($catalog['type_error_i18n']));
        $this->assertTrue(isset($catalog['min_error_i18n']));
        $this->assertTrue(isset($catalog['max_error_i18n']));
        $this->assertTrue(isset($catalog['regexp_error_i18n']));
        $this->assertTrue(isset($catalog['name_i18n_all']));
        $this->assertTrue(isset($catalog['required_error_i18n_all']));
        $this->assertTrue(isset($catalog['type_error_i18n_all']));
        $this->assertTrue(isset($catalog['min_error_i18n_all']));
        $this->assertTrue(isset($catalog['max_error_i18n_all']));
        $this->assertTrue(isset($catalog['regexp_error_i18n_all']));
        
        $this->assertTrue(isset($catalog['actionform filter']));

        //  assert Action
        $this->assertTrue(isset($catalog['action prepare']));
        $this->assertTrue(isset($catalog["action\nprepare\n multiple\n  line"]));
        
        $this->assertTrue(isset($catalog['action perform']));
    } 

    function test_View()
    {
        $skel = ETHNA_TEST_SKELDIR . 'skel.view.i18ntest.php';   
        $r = $this->proj->runCmd('add-view',
                          array(
                              '-s', $skel,
                              'i18n', 
                          ) 
             );
        if (Ethna::isError($r)) {
            $this->fail($r->getMessage());
        }
        $this->run_i18n_cmd();
        $catalog = $this->i18n->parseEthnaMsgCatalog($this->msg_file);

        //  assert view 
        $this->assertTrue(isset($catalog['view global']));
        $this->assertTrue(isset($catalog['view prepare']));
        $this->assertTrue(isset($catalog["view\n\n   prepare\n multiple\n  line"]));
    } 

    function test_Template()
    {
        $skel = ETHNA_TEST_SKELTPLDIR . 'skel.template.i18ntest.tpl';   
        $r = $this->proj->runCmd('add-template',
                          array(
                              '-s', $skel,
                              'i18n', 
                          ) 
             );
        if (Ethna::isError($r)) {
            $this->fail($r->getMessage());
        }
        $this->run_i18n_cmd();
        $catalog = $this->i18n->parseEthnaMsgCatalog($this->msg_file);

        //  assert template 
        $this->assertTrue(isset($catalog['template i18n']));
        $this->assertTrue(isset($catalog['template i18n modifier']));
        $this->assertTrue(isset($catalog['template i18n multiple modifier']));
    } 

    function test_cmd_option()
    {
        //    unrecognized option
        $r = $this->proj->runCmd('i18n', array('-k'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option -k', $r->getMessage());

        //    --locale(requires an argument)
        $r = $this->proj->runCmd('i18n', array('-l'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option -l requires an argument', $r->getMessage());

        $r = $this->proj->runCmd('i18n', array('-l', 'ko_KR'));
        $this->assertFalse(Ethna::isError($r));
        
        $r = $this->proj->runCmd('i18n', array('--locale'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option --locale requires an argument', $r->getMessage());

        //    --gettext option only
        $r = $this->proj->runCmd('i18n', array('-g'));
        $this->assertFalse(Ethna::isError($r));

        $r = $this->proj->runCmd('i18n', array('--gettext'));
        $this->assertFalse(Ethna::isError($r));

        //    --gettext not allowed an argument 
        $r = $this->proj->runCmd('i18n', array('--gettext=foo'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual("option --gettext doesn't allow an argument", $r->getMessage());

        //    --locale and --gettext mixin
        $r = $this->proj->runCmd('i18n', array('-g', '-l', 'ko_KR'));
        $this->assertFalse(Ethna::isError($r));

        $r = $this->proj->runCmd('i18n', array('--gettext', '--locale=ko_KR'));
        $this->assertFalse(Ethna::isError($r));
    }
   
    function run_i18n_cmd()
    {
        $r = $this->proj->runCmd('i18n');
        if (Ethna::isError($r)) {
            $this->fail($r->getMessage());
            return;
        }
    } 


}
// }}}

