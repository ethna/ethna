<?php
// vim: foldmethod=marker
/**
 *  Plugin_Handle_AddTemplate_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/test/MockProject.php';

//{{{  Ethna_Plugin_Handle_AddTemplate_Test
/**
 *  Test Case For Ethna_Plugin_Handle_AddTemplate_Test
 *
 *  @access public
 */
class Ethna_Plugin_Handle_AddTemplate_Test extends Ethna_UnitTestBase 
{
    var $proj;

    function setUp()
    {
        $this->proj = new Ethna_MockProject();
        $r = $this->proj->create();
        if (Ethna::isError($r)) {
            $this->fail($r->getMessage());    
        }
    }

    function tearDown()
    {
        $this->proj->delete();
    }

    function test_template_dir_exists()
    {
        $ctl = $this->proj->getController(); 

        //    default locale 
        $r = $this->proj->runCmd('add-template', array('test'));
        $template_dir = $ctl->getTemplatedir();
        $this->assertTrue(file_exists($template_dir));

        //    new locale 
        $r = $this->proj->runCmd('add-template', array('-l', 'en_US', 'test'));
        $template_dir = $ctl->getTemplatedir();
        $this->assertTrue(file_exists($template_dir));
    }

    function test_cmd_option()
    {
        //    unrecognized option
        $r = $this->proj->runCmd('add-template', array('-k'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('unrecognized option -k', $r->getMessage());

        //    skel requires an argument
        $r = $this->proj->runCmd('add-template', array('-s'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option -s requires an argument', $r->getMessage());

        $r = $this->proj->runCmd('add-template', array('--skelfile'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option --skelfile requires an argument', $r->getMessage());

        //    locale requires an argument
        $r = $this->proj->runCmd('add-template', array('-l'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option -l requires an argument', $r->getMessage());

        $r = $this->proj->runCmd('add-template', array('--locale'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('option --locale requires an argument', $r->getMessage());

        //    template name isn't set
        $r = $this->proj->runCmd('add-template', array());
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('template name isn\'t set.', $r->getMessage());

        //    invalid locale
        $r = $this->proj->runCmd('add-template', array('-l', 'invalid::locale', 'test'));
        $this->assertTrue(Ethna::isError($r));
        $this->assertEqual('You specified locale, but invalid : invalid::locale', $r->getMessage());

        //    normal command exexute
        $r = $this->proj->runCmd('add-template', array('-l', 'ja_JP', 'test'));
        $this->assertFalse(Ethna::isError($r));
    }
}
// }}}

