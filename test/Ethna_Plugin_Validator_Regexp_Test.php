<?php
/**
 *  Ethna_Plugin_Validator_Regexp_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Regexpクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Regexp_Test extends Ethna_UnitTestBase
{
    function testCheckValidatorRegexp()
    {
        $ctl =& Ethna_Controller::getInstance();
        $plugin =& $ctl->getPlugin();
        $vld = $plugin->getPlugin('Validator', 'Regexp');


        $form_string = array(
                             'type'          => VAR_TYPE_STRING,
                             'required'      => true,
                             'regexp'        => '/^[a-zA-Z]+$/',
                             'error'         => '{form}を正しく入力してください'
                             );
        $vld->af->setDef('namae_string', $form_string);

        $pear_error = $vld->validate('namae_string', 'fromA', $form_string);
        $this->assertFalse(is_a($pear_error, 'PEAR_Error'));

        // 許されていない文字列
        $pear_error = $vld->validate('namae_string', '7.6', $form_string);
        $this->assertTrue(is_a($pear_error, 'PEAR_Error'));
        $this->assertEqual(E_FORM_REGEXP, $pear_error->getCode());
        $this->assertEqual($form_string['error'], $pear_error->getMessage());

        $pear_error = $vld->validate('namae_string', '', $form_string);
        // requiredとの関係上
        $this->assertFalse(is_a($pear_error, 'PEAR_Error'));

    }
}
?>
