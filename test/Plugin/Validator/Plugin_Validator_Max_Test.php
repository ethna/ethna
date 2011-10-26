<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Max_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Maxクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Max_Test extends Ethna_UnitTestBase
{
    public $vld;
    public $ctl;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
        $plugin = $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Max');

        $this->ctl = $ctl;
    }

    // {{{ test max integer
    function test_max_integer()
    {
        $form_int = array(
                          'type'          => VAR_TYPE_INT,
                          'required'      => true,
                          'max'           => '10',
                          'error'         => '{form}には10以下の数字(整数)を入力して下さい'
                          );
        $af = $this->ctl->getActionForm();
        $af->setDef('namae_int', $form_int);

        $pear_error = $this->vld->validate('namae_int', 9, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', 10, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', '', $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', 9.5, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた値
        $pear_error = $this->vld->validate('namae_int', 11, $form_int);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MAX_INT,$pear_error->getCode());
        $this->assertEqual($form_int['error'], $pear_error->getMessage());
    } 
    // }}}

    // {{{ test max float
    function test_max_float()
    {
        $form_float = array(
                            'type'          => VAR_TYPE_FLOAT,
                            'required'      => true,
                            'max'           => '10.000000',
                            'error'         => '{form}には10.000000以下の数字(小数)を入力して下さい'
                            );
        $af = $this->ctl->getActionForm();
        $af->setDef('namae_float', $form_float);

        $pear_error = $this->vld->validate('namae_float', 10, $form_float);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_float', '', $form_float);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた値
        $pear_error = $this->vld->validate('namae_float', 10.11, $form_float);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MAX_FLOAT, $pear_error->getCode());
        $this->assertEqual($form_float['error'], $pear_error->getMessage());

        // maxを超えた値
        $pear_error = $this->vld->validate('namae_float', 11, $form_float);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MAX_FLOAT, $pear_error->getCode());
        $this->assertEqual($form_float['error'], $pear_error->getMessage());
    }
    // }}}

    // {{{ test max string
    function test_max_string()
    {
        $form_string = array(
                             'type'          => VAR_TYPE_STRING,
                             'required'      => true,
                             'max'           => '2',
                             'error'         => '{form}は全角2文字以下(半角1文字以下)で入力して下さい'
                             );
        $af = $this->ctl->getActionForm();
        $af->setDef('namae_string', $form_string);

        $pear_error = $this->vld->validate('namae_string', '', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', 'as', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた文字列長
        $pear_error = $this->vld->validate('namae_string', 'ddd', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MAX_STRING, $pear_error->getCode());
        $this->assertEqual($form_string['error'], $pear_error->getMessage());

        // maxを超えた文字列長
        $pear_error = $this->vld->validate('namae_string', 118888, $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MAX_STRING, $pear_error->getCode());
        $this->assertEqual($form_string['error'], $pear_error->getMessage());

        // multibyte string.
        $pear_error = $this->vld->validate('namae_string', 'ああ', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));
 
        $pear_error = $this->vld->validate('namae_string', 'あああ', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
    }
    // }}}

    // {{{ test max datetime
    function test_max_datetime()
    {
        $form_datetime = array(
                               'type'          => VAR_TYPE_DATETIME,
                               'required'      => true,
                               'max'           => '-1 day',
                               'error'         => '{form}には-1 day以前の日付を入力して下さい'
                               );
        $af = $this->ctl->getActionForm();
        $af->setDef('namae_datetime', $form_datetime);

        $pear_error = $this->vld->validate('namae_datetime', '-2 day', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_datetime', '-1 day', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_datetime', '', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた日付
        $pear_error = $this->vld->validate('namae_datetime', '+3 day', $form_datetime);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MAX_DATETIME, $pear_error->getCode());
        $this->assertEqual($form_datetime['error'], $pear_error->getMessage());
    }
    // }}}

}
