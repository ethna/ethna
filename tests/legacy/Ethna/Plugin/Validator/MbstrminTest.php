<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_Mbstrmin_Test extends PHPUnit_Framework_Testcase
{
    public $vld;
    public $ctl;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();
        $this->url_handler = new Ethna_UrlHandler_Simple_TestClass($this);
        $plugin = $this->controller->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Mbstrmin');
    }

    public function test_min_mbstr()
    {
        $form_mbstr = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'mbstrmin'      => '3',
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_mbstr', $form_mbstr);

        $pear_error = $this->vld->validate('namae_mbstr', 'あいう', $form_mbstr);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_mbstr', 'あい', $form_mbstr);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_STRING,$pear_error->getCode());

        //  TODO: Error Message Test.
    }
}

