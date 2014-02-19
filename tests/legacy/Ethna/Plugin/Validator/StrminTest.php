<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_Strmin_Test extends PHPUnit_Framework_TestCase
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
        $this->vld = $plugin->getPlugin('Validator', 'Strmin');
    }

    public function test_min_str()
    {
        $form_str = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'strmin'      => '3',
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_str', $form_str);

        $pear_error = $this->vld->validate('namae_str', 'abc', $form_str);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_str', 'ab', $form_str);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_STRING,$pear_error->getCode());

        //  TODO: Error Message Test.
    }
}

