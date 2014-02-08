<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_Regexp_Test extends PHPUnit_Framework_TestCase
{
    public $vld;
    public $controller;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();
        $this->url_handler = new Ethna_UrlHandler_Simple_TestClass($this);
        $plugin = $this->controller->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Regexp');
    }

    public function test_regexp_string()
    {
        $form_string = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'regexp'        => '/^[a-zA-Z]+$/',
            'error'         => '{form}を正しく入力してください'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_string', $form_string);

        $pear_error = $this->vld->validate('namae_string', 'fromA', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // 許されていない文字列
        $pear_error = $this->vld->validate('namae_string', '7.6', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REGEXP, $pear_error->getCode());
        $this->assertEquals($form_string['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_string', '', $form_string);
        // requiredとの関係上
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));
    }
}

