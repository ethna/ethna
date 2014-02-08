<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_Mbregexp_Test extends PHPUnit_Framework_TestCase
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
        $this->vld = $plugin->getPlugin('Validator', 'Mbregexp');
    }

    public function test_Validate_Mbregexp()
    {
        $form_def = array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'required' => true,
            'mbregexp' => '^[あ-ん]+$',  // 全角ひらがなonly
            'mbregexp_encoding' => 'UTF-8',
        );
        $af = $this->controller->getActionForm();
        $af->setDef('input', $form_def);

        $pear_error = $this->vld->validate('input', 9, $form_def);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('input', 'あいう', $form_def);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        //    encoding に指定された文字コード以外の文字列
        $euc_input = mb_convert_encoding('あいう', 'EUC-JP', 'UTF-8');
        $pear_error = $this->vld->validate('input', $euc_input, $form_def);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
    }
}
