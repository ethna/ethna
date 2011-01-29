<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Strmin_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Strminクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Strmin_Test extends Ethna_UnitTestBase
{
    public $vld;
    public $ctl;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
        $plugin = $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Strmin');

        $this->ctl = $ctl;
    }

    // {{{ test min str 
    function test_min_str()
    {
        $form_str = array(
                          'type'          => VAR_TYPE_STRING,
                          'required'      => true,
                          'strmin'      => '3',
                          );
        $af = $this->ctl->getActionForm();
        $af->setDef('namae_str', $form_str);

        $pear_error = $this->vld->validate('namae_str', 'abc', $form_str);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_str', 'ab', $form_str);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MIN_STRING,$pear_error->getCode());

        //  TODO: Error Message Test.
    } 
    // }}}

}

