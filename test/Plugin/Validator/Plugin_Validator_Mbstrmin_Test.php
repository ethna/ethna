<?php
// vim: foldmethod=marker
/**
 *  Plugin_Validator_Mbstrmin_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Mbstrminクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Validator_Mbstrmin_Test extends Ethna_UnitTestBase
{
    public $vld;
    public $ctl;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
        $plugin = $ctl->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Mbstrmin');

        $this->ctl = $ctl;
    }

    // {{{ test min mbstr 
    function test_min_mbstr()
    {
        $form_mbstr = array(
                          'type'          => VAR_TYPE_STRING,
                          'required'      => true,
                          'mbstrmin'      => '3',
                          );
        $af = $this->ctl->getActionForm();
        $af->setDef('namae_mbstr', $form_mbstr);

        $pear_error = $this->vld->validate('namae_mbstr', 'あいう', $form_mbstr);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_mbstr', 'あい', $form_mbstr);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEqual(E_FORM_MIN_STRING,$pear_error->getCode());

        //  TODO: Error Message Test.
    } 
    // }}}

}

