<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_Max_Test extends PHPUnit_Framework_TestCase
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
        $this->vld = $plugin->getPlugin('Validator', 'Max');
    }

    public function test_max_integer()
    {
        $form_int = array(
            'type'          => VAR_TYPE_INT,
            'required'      => true,
            'max'           => '10',
            'error'         => '{form}には10以下の数字(整数)を入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_int', $form_int);

        $pear_error = $this->vld->validate('namae_int', 9, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', 10, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', '', $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', 9.5, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた値
        $pear_error = $this->vld->validate('namae_int', 11, $form_int);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MAX_INT,$pear_error->getCode());
        $this->assertEquals($form_int['error'], $pear_error->getMessage());
    }

    public function test_max_float()
    {
        $form_float = array(
            'type'          => VAR_TYPE_FLOAT,
            'required'      => true,
            'max'           => '10.000000',
            'error'         => '{form}には10.000000以下の数字(小数)を入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_float', $form_float);

        $pear_error = $this->vld->validate('namae_float', 10, $form_float);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_float', '', $form_float);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた値
        $pear_error = $this->vld->validate('namae_float', 10.11, $form_float);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MAX_FLOAT, $pear_error->getCode());
        $this->assertEquals($form_float['error'], $pear_error->getMessage());

        // maxを超えた値
        $pear_error = $this->vld->validate('namae_float', 11, $form_float);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MAX_FLOAT, $pear_error->getCode());
        $this->assertEquals($form_float['error'], $pear_error->getMessage());
    }

    public function test_max_string()
    {
        $form_string = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'max'           => '2',
            'error'         => '{form}は全角2文字以下(半角1文字以下)で入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_string', $form_string);

        $pear_error = $this->vld->validate('namae_string', '', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', 'as', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた文字列長
        $pear_error = $this->vld->validate('namae_string', 'ddd', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MAX_STRING, $pear_error->getCode());
        $this->assertEquals($form_string['error'], $pear_error->getMessage());

        // maxを超えた文字列長
        $pear_error = $this->vld->validate('namae_string', 118888, $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MAX_STRING, $pear_error->getCode());
        $this->assertEquals($form_string['error'], $pear_error->getMessage());

        // multibyte string.
        $pear_error = $this->vld->validate('namae_string', 'ああ', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', 'あああ', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
    }

    public function test_max_datetime()
    {
        $form_datetime = array(
            'type'          => VAR_TYPE_DATETIME,
            'required'      => true,
            'max'           => '-1 day',
            'error'         => '{form}には-1 day以前の日付を入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_datetime', $form_datetime);

        $pear_error = $this->vld->validate('namae_datetime', '-2 day', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_datetime', '-1 day', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_datetime', '', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // maxを超えた日付
        $pear_error = $this->vld->validate('namae_datetime', '+3 day', $form_datetime);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MAX_DATETIME, $pear_error->getCode());
        $this->assertEquals($form_datetime['error'], $pear_error->getMessage());
    }
}
