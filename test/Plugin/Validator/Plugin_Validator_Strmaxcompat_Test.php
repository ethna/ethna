<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Strmaxcompat_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Strmaxcompatクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Strmaxcompat_Test extends Ethna_UnitTestBase
{
    public $vld;
    public $ctl;

    function setUp()
    {
        $ctl = new Ethna_Controller();
        $ctl->setClientEncoding('EUC-JP');
        $ctl->setActionForm(new Ethna_ActionForm($ctl));
        $plugin = $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Strmaxcompat');

        $this->ctl = $ctl;
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    // {{{ test max str (compatible class, EUC-JP)
    function test_max_str_compat_euc()
    {
        if (extension_loaded('mbstring')) {
            $form_str = array(
                              'type'          => VAR_TYPE_STRING,
                              'required'      => true,
                              'strmaxcompat'  => '4',  // 半角4、全角2文字
                        );
            $af = $this->ctl->getActionForm();
            $af->setDef('namae_str', $form_str);

            //    ascii.
            $input_str = 'abcd';
            $pear_error = $this->vld->validate('namae_str', $input_str, $form_str);
            $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

            $error_str = 'abcde';
            $pear_error = $this->vld->validate('namae_str', $error_str, $form_str);
            $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
            $this->assertEqual(E_FORM_MAX_STRING,$pear_error->getCode());

            //    multibyte string
            $input_str = 'あい';
            $input_str_euc = mb_convert_encoding($input_str, 'EUC-JP', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $input_str_euc, $form_str);
            $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

            $error_str = 'あいう';
            $error_str_euc = mb_convert_encoding($error_str, 'EUC-JP', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $error_str_euc, $form_str);
            $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
            $this->assertEqual(E_FORM_MAX_STRING,$pear_error->getCode());

        } else {
            echo " ... skipped because mbstring extension is not installed.";
        }

        //  TODO: Error Message Test.
    }
    // }}}

    // {{{ test max str (compatible class, SJIS)
    function test_max_str_compat_sjis()
    {
        if (extension_loaded('mbstring')) {

            $this->ctl->setClientEncoding('SJIS');
            $form_str = array(
                              'type'          => VAR_TYPE_STRING,
                              'required'      => true,
                              'strmaxcompat'  => '4',  // 半角4、全角2文字
                        );
            $af = $this->ctl->getActionForm();
            $af->setDef('namae_str', $form_str);

            //    ascii.
            $input_str = 'abcd';
            $pear_error = $this->vld->validate('namae_str', $input_str, $form_str);
            $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

            $error_str = 'abcde';
            $pear_error = $this->vld->validate('namae_str', $error_str, $form_str);
            $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
            $this->assertEqual(E_FORM_MAX_STRING,$pear_error->getCode());

            //    multibyte string
            $input_str = 'あい';
            $input_str_sjis = mb_convert_encoding($input_str, 'SJIS', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $input_str_sjis, $form_str);
            $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

            $error_str = 'あいう';
            $error_str_sjis = mb_convert_encoding($error_str, 'SJIS', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $error_str_sjis, $form_str);
            $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
            $this->assertEqual(E_FORM_MAX_STRING,$pear_error->getCode());

        } else {
            echo " ... skipped because mbstring extension is not installed.";
        }

        //  TODO: Error Message Test.
    }
    // }}}
}

