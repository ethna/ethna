<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_Required_Test extends PHPUnit_Framework_Testcase
{
    public $vld;
    public $controller;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->controller->setClientEncoding('EUC-JP');
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();
        $this->url_handler = new Ethna_UrlHandler_Simple_TestClass($this);
        $plugin = $this->controller->getPlugin();
        $this->vld = $plugin->getPlugin('Validator', 'Required');
    }

    public function test_formtext()
    {
        //    required => false でテキストフォームに文字列を入力する場合
        $form_string = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => false,
            'form_type'     => FORM_TYPE_TEXT,
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_string', $form_string);

        $pear_error = $this->vld->validate('namae_string', 10, $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', '', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', false, $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', null, $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        //    required => true でテキストフォームに文字列を入力する場合
        $form_string = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'form_type'     => FORM_TYPE_TEXT,
            'error'         => 'フォーム値必須エラー'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_string', $form_string);

        $pear_error = $this->vld->validate('namae_string', 10, $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // 必須フォームに入力がない
        $pear_error = $this->vld->validate('namae_string', '', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_string['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_string', false, $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals($form_string['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_string', null, $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals($form_string['error'], $pear_error->getMessage());
    }

    public function test_formselect()
    {
        $form_select = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => false,
            'form_type'     => FORM_TYPE_SELECT,
            'error'         => 'フォーム値必須エラー'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_select', $form_select);

        $pear_error = $this->vld->validate('namae_select', 'selection', $form_select);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_select', '', $form_select);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_select', false, $form_select);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_select', null, $form_select);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $form_select = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'form_type'     => FORM_TYPE_SELECT,
            'error'         => 'フォーム値必須エラー'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_select', $form_select);

        $pear_error = $this->vld->validate('namae_select', 'selection', $form_select);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // 必須フォームが選択されない
        $pear_error = $this->vld->validate('namae_select', '', $form_select);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_select['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_select', false, $form_select);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_select['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_select', null, $form_select);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_select['error'], $pear_error->getMessage());
    }

    public function test_formradio()
    {
        $form_radio = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => false,
            'form_type'     => FORM_TYPE_RADIO,
            'error'         => 'フォーム値必須エラー'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_radio', $form_radio);

        $pear_error = $this->vld->validate('namae_radio', 'radio', $form_radio);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_radio', '', $form_radio);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_radio', false, $form_radio);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_radio', null, $form_radio);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));


        $form_radio = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'form_type'     => FORM_TYPE_RADIO,
            'error'         => 'フォーム値必須エラー'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_radio', $form_radio);

        $pear_error = $this->vld->validate('namae_radio', 'radio', $form_radio);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // 必須フォームが選択されない
        $pear_error = $this->vld->validate('namae_radio', '', $form_radio);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_radio['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_radio', false, $form_radio);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_radio['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_radio', null, $form_radio);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_radio['error'], $pear_error->getMessage());
    }

    public function test_formcheckbox()
    {
        $form_checkbox = array(
            'required'      => false,
            'form_type'     => FORM_TYPE_CHECKBOX,
            'type'          => array(VAR_TYPE_BOOLEAN),
            'error'         => 'フォーム値必須エラー',
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_checkbox', $form_checkbox);

        $checks = array(
            '1st' => 0,
            '2nd' => 1,
            '3rd' => 3,
            '4th' => 'value'
        );
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $checks = array();
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_checkbox', null, $form_checkbox);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_checkbox', false, $form_checkbox);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));


        $form_checkbox = array(
            'required'      => true,
            'form_type'     => FORM_TYPE_CHECKBOX,
            'type'          => array(VAR_TYPE_BOOLEAN),
            'error'         => 'フォーム値必須エラー',
        );
        $af->setDef('namae_checkbox', $form_checkbox);

        $checks = array(
            '1st' => 0,
            '2nd' => 1,
            '3rd' => 3,
            '4th' => 'value'
        );
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // 必須フォームが選択されない
        $checks = array();
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_checkbox['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_checkbox', null, $form_checkbox);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_checkbox['error'], $pear_error->getMessage());

        $pear_error = $this->vld->validate('namae_checkbox', false, $form_checkbox);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_checkbox['error'], $pear_error->getMessage());


        $form_checkbox = array(
            'required'      => true,
            'form_type'     => FORM_TYPE_CHECKBOX,
            'type'          => array(VAR_TYPE_BOOLEAN),
            'error'         => 'フォーム値必須エラー',
            'key'           => '4th',
            'num'           => 4,
        );
        $af->setDef('namae_checkbox', $form_checkbox);

        $checks = array(
            '1st' => 0,
            '2nd' => 1,
            '3rd' => 3,
            '4th' => 'value'
        );
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // 何らかの処理でfalseに書き換えてしまった場合はエラー
        $checks = array(
            '1st' => 0,
            '2nd' => 1,
            '3rd' => 3,
            '4th' => false
        );
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_checkbox['error'], $pear_error->getMessage());

        // num error
        $checks = array(
            '1st' => 0,
            '2nd' => 1,
            '3rd' => 'value'
        );
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_checkbox['error'], $pear_error->getMessage());

        // key error
        $checks = array(
            '1st' => 0,
            '2nd' => 'value',
            '3rd' => 2,
            '4' => 3
        );
        $pear_error = $this->vld->validate('namae_checkbox', $checks, $form_checkbox);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_REQUIRED, $pear_error->getCode());
        $this->assertEquals($form_checkbox['error'], $pear_error->getMessage());
    }
}

