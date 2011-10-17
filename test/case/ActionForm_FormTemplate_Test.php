<?php
// vim: foldmethod=marker
/**
 *  ActionForm_FormTemplate_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{  Ethna_FormTemplate_ActionForm
/**
 *  Test ActionForm (Form Template) 
 *
 *  @access public
 */
class Ethna_FormTemplate_ActionForm extends Ethna_ActionForm
{
    var $form_template = array(
       'normal' => array(
           'name'      => '通常のフォームテンプレート用定義',
           'required'  => false,
           'form_type' => FORM_TYPE_SELECT,
           'type'      => VAR_TYPE_INT,
       ),
       'syntax_sugar' => array(
           'name'      => 'シンタックスシュガー用定義',
           'required'  => true,
           'form_type' => FORM_TYPE_TEXT,
           'type'      => VAR_TYPE_STRING,
       ),
    );
}
// }}}

// {{{  Ethna_FormTemplateTest_ActionForm
/**
 *  Test ActionForm (Form Template) 
 *
 *  @access public
 */
class Ethna_FormTemplateTest_ActionForm extends Ethna_FormTemplate_ActionForm
{
    var $form = array(
       'normal' => array(),
       'syntax_sugar',  //  シンタックスシュガー
    );
}
// }}}

// {{{  Ethna_ActionForm_FormTemplate_Test
/**
 *  Test Case For Ethna_ActionForm(Form Template)
 *
 *  @access public
 */
class Ethna_ActionForm_FormTemplate_Test extends Ethna_UnitTestBase
{
    var $local_af;

    function setUp()
    {
        //   REQUEST_METHOD を設定しないと
        //   フォームテンプレートが初期化されない
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->local_af = new Ethna_FormTemplateTest_ActionForm($this->ctl); 
    }

    function tearDown()
    {
        $_SERVER['REQUEST_METHOD'] = NULL;
        $this->local_af = NULL;
    }

    // {{{ normal form template
    function test_formtemplate_normal()
    {
        $normal_def = $this->local_af->getDef('normal');
        $this->assertEqual($normal_def['name'], '通常のフォームテンプレート用定義');
        $this->assertEqual($normal_def['required'], false);
        $this->assertEqual($normal_def['form_type'], FORM_TYPE_SELECT);
        $this->assertEqual($normal_def['type'], VAR_TYPE_INT);
    }
    // }}}

    // {{{ syntax sugar 
    function test_formtemplate_syntaxsugar()
    {
        $syntax_sugar_def = $this->local_af->getDef('syntax_sugar');
        $this->assertEqual($syntax_sugar_def['name'], 'シンタックスシュガー用定義');
        $this->assertEqual($syntax_sugar_def['required'], true);
        $this->assertEqual($syntax_sugar_def['form_type'], FORM_TYPE_TEXT);
        $this->assertEqual($syntax_sugar_def['type'], VAR_TYPE_STRING);
    }
    // }}}
}
// }}}
