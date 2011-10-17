<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Strmincompat_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Strmincompatクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Strmincompat_Test extends Ethna_UnitTestBase
{
    private $vld;
    private $local_ctl;

    function setUp()
    {
        $ctl = new Ethna_Controller();
        $ctl->setClientEncoding('EUC-JP');
        $ctl->setActionForm(new Ethna_ActionForm($ctl));
        $this->local_ctl = $ctl;
        $plugin = $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Strmincompat');
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    // {{{ test min str (compatible class, EUC-JP)
    function test_min_str_compat_euc()
    {
        if (extension_loaded('mbstring')) {
            $form_str = array(
                'type'          => VAR_TYPE_STRING,
                'required'      => true,
                'strmincompat'  => '4',  // 半角4、全角2文字
            );
            $af = $this->local_ctl->getActionForm();
            $af->setDef('namae_str', $form_str);

            //    ascii.
            $input_str = 'abcd';
            $pear_error = $this->vld->validate('namae_str', $input_str, $form_str);
            $this->assertFalse($pear_error instanceof Ethna_Error);

            $error_str = 'abc';
            $pear_error = $this->vld->validate('namae_str', $error_str, $form_str);
            $this->assertTrue($pear_error instanceof Ethna_Error);
            $this->assertEqual(E_FORM_MIN_STRING, $pear_error->getCode());

            //    multibyte string
            $input_str = 'あい';
            $input_str_euc = mb_convert_encoding($input_str, 'EUC-JP', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $input_str_euc, $form_str);
            $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

            $error_str = 'あ';
            $error_str_euc = mb_convert_encoding($error_str, 'EUC-JP', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $error_str_euc, $form_str);
            $this->assertTrue($pear_error instanceof Ethna_Error);
            $this->assertEqual(E_FORM_MIN_STRING,$pear_error->getCode());

        } else {
            echo " ... skipped because mbstring extension is not installed.";
        }

        //  TODO: Error Message Test.
    }
    // }}}

    // {{{ test min str (compatible class, SJIS)
    function test_min_str_compat_sjis()
    {
        if (extension_loaded('mbstring')) {

            $this->local_ctl->setClientEncoding('SJIS');

            $form_str = array(
                              'type'          => VAR_TYPE_STRING,
                              'required'      => true,
                              'strmincompat'  => '4',  // 半角4、全角2文字
                        );
            $af = $this->local_ctl->getActionForm();
            $af->setDef('namae_str', $form_str);

            //    ascii.
            $input_str = 'abcd';
            $pear_error = $this->vld->validate('namae_str', $input_str, $form_str);
            $this->assertFalse($pear_error instanceof Ethna_Error);

            $error_str = 'abc';
            $pear_error = $this->vld->validate('namae_str', $error_str, $form_str);
            $this->assertTrue($pear_error instanceof Ethna_Error);
            $this->assertEqual(E_FORM_MIN_STRING,$pear_error->getCode());

            //    multibyte string(sjis)
            $input_str = 'あい';
            $input_str_sjis = mb_convert_encoding($input_str, 'SJIS', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $input_str_sjis, $form_str);
            $this->assertFalse($pear_error instanceof Ethna_Error);

            $error_str = 'あ';
            $error_str_sjis = mb_convert_encoding($error_str, 'SJIS', 'UTF-8');
            $pear_error = $this->vld->validate('namae_str', $error_str_sjis, $form_str);
            $this->assertTrue($pear_error instanceof Ethna_Error);
            $this->assertEqual(E_FORM_MIN_STRING,$pear_error->getCode());

        } else {
            echo " ... skipped because mbstring extension is not installed.";
        }

        //  TODO: Error Message Test.
    }
    // }}}
}

