<?php
/**
 *  ViewClass_FormHelper_Test.php
 *
 *  @package Ethna
 *  @author Yoshinari Takaoka <takaoka@beatcraft.com>
 */

require_once ETHNA_INSTALL_BASE . '/test/MockProject.php';

/**
 *  Ethna_ViewClass のうち、フォームヘルパ
 *  に関連するテストケースを集めたクラス
 *
 *  @package Ethna
 *  @author Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access public
 */
class Ethna_ViewClass_FormHelper_Test extends Ethna_UnitTestBase
{
    var $project;

    function setUp()
    {
        $this->project = new Ethna_MockProject();
        $this->project->create();
    }

    function tearDown()
    {
        $this->project->delete();
        unset($GLOBALS['_Ethna_controller']);
    }

    function test_formhelper_Text()
    {
        $action_name = $tpl_name = 'texttest';
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.formhelper.php';
        $this->project->runCmd('add-action',
                               array(
                                   '-s',
                                   $action_skel,
                                   $action_name,
                               )
        );
        $tpl_skel = ETHNA_TEST_SKELTPLDIR . 'skel.template.text.tpl';
        $this->project->runCmd('add-template',
                               array(
                                   '-s',
                                   $tpl_skel,
                                   $tpl_name,
                               )
        );
        $submit_value = array(
            'text_setactval' => 'abcd',
        );
        $result = $this->project->runMain($action_name, $submit_value);
        $this->assertPattern('#<input type="text" name="text_noval" value="" />#', $result);
        $this->assertPattern('#<input type="text" name="text_setactval" value="abcd" />#', $result);
        $this->assertPattern('#<input value="1234" type="text" name="text_settplval" />#', $result);
    }

    function test_formhelper_Textarea()
    {
        $action_name = $tpl_name = 'textareatest';
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.formhelper.php';
        $this->project->runCmd('add-action',
                               array(
                                   '-s',
                                   $action_skel,
                                   $action_name,
                               )
        );
        $tpl_skel = ETHNA_TEST_SKELTPLDIR . 'skel.template.textarea.tpl';
        $this->project->runCmd('add-template',
                               array(
                                   '-s',
                                   $tpl_skel,
                                   $tpl_name,
                               )
        );
        $submit_value = array(
            'textarea_setactval' => 'input',
        );
        $result = $this->project->runMain($action_name, $submit_value);

        $this->assertPattern('#<textarea name="textarea_noval"></textarea>#', $result);
        $this->assertPattern('#<textarea name="textarea_setactval">input</textarea>#', $result);
        $this->assertPattern('#<textarea value="foo" name="textarea_settplval">foo</textarea>#', $result);
    }

    function test_formhelper_FormName()
    {
        $action_name = $tpl_name = 'formnametest';
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.formhelper.php';
        $this->project->runCmd('add-action',
                               array(
                                   '-s',
                                   $action_skel,
                                   $action_name,
                               )
        );
        $tpl_skel = ETHNA_TEST_SKELTPLDIR . 'skel.template.blockform.tpl';
        $this->project->runCmd('add-template',
                               array(
                                   '-s',
                                   $tpl_skel,
                                   $tpl_name,
                               )
        );
        $result = $this->project->runMain($action_name, array());

        $this->assertPattern('#<form name="hoge" method="post"><input type="hidden" name="ethna_fid" value="hoge" />#', $result);
    }

}

