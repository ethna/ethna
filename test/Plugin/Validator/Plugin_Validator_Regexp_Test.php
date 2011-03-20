<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Regexp_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Regexpクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Regexp_Test extends Ethna_UnitTestBase
{
    public $vld;
    public $ctl;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
        $plugin = $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Regexp');

        $this->ctl = $ctl;
    }

    // {{{  test regexp string
    function test_regexp_string()
    {
        $form_string = array(
                             'type'          => VAR_TYPE_STRING,
                             'required'      => true,
                             'regexp'        => '/^[a-zA-Z]+$/',
                             'error'         => '{form}を正しく入力してください'
                             );
        $af = $this->ctl->getActionForm();
        $af->setDef('namae_string', $form_string);

        $pear_error = $this->vld->validate('namae_string', 'fromA', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // 許されていない文字列
        $pear_error = $this->vld->validate('namae_string', '7.6', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_REGEXP, $pear_error->getCode());
        $this->assertEqual($form_string['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_string', '', $form_string);
        // requiredとの関係上
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));
    }
    // }}}
}

