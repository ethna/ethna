<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_StrmaxTest extends PHPUnit_Framework_Testcase
{
    public $vld;
    public $ctl;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->controller->setClientEncoding('EUC-JP');
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();
        $plugin = $this->controller->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Strmax');
    }

    public function test_max_str()
    {
        $form_str = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'strmax'      => '3',
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_mbstr', $form_str);

        $pear_error = $this->vld->validate('namae_mbstr', 'abc', $form_str);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_mbstr', 'abcd', $form_str);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MAX_STRING,$pear_error->getCode());

        //  TODO: Error Message Test.
    }
}

