<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Mbregexp_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_Plugin_Validator_Mbregexp_Test
/**
 *  Test Case For Ethna_ActionForm(Mbegexp Validator)
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Mbregexp_Test extends Ethna_UnitTestBase
{
    public $vld;
    public $ctl;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
        $plugin = $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Mbregexp');

        $this->ctl = $ctl;
    }

    // {{{ Validator Mbregexp.
    function test_Validate_Mbregexp()
    {
        $form_def = array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'required' => true,
            'mbregexp' => '^[あ-ん]+$',  // 全角ひらがなonly
            'mbregexp_encoding' => 'UTF-8',
        );
        $af = $this->ctl->getActionForm();
        $af->setDef('input', $form_def);

        $pear_error = $this->vld->validate('input', 9, $form_def);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('input', 'あいう', $form_def);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        //    encoding に指定された文字コード以外の文字列
        $euc_input = mb_convert_encoding('あいう', 'EUC-JP', 'UTF-8');
        $pear_error = $this->vld->validate('input', $euc_input, $form_def);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
    }
    // }}}

}
// }}}

