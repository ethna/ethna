<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Strmax_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Strmaxクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Strmax_Test extends Ethna_UnitTestBase
{
    var $vld;

    function setUp()
    {
        $ctl =& Ethna_Controller::getInstance();
        $plugin =& $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Strmax');
    }

    // {{{ test max str 
    function test_max_str()
    {
        $form_str = array(
                          'type'          => VAR_TYPE_STRING,
                          'required'      => true,
                          'strmax'      => '3',
                          );
        $this->vld->af->setDef('namae_mbstr', $form_str);

        $pear_error = $this->vld->validate('namae_mbstr', 'abc', $form_str);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_mbstr', 'abcd', $form_str);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MAX_STRING,$pear_error->getCode());

        //  TODO: Error Message Test.
    } 
    // }}}

}

