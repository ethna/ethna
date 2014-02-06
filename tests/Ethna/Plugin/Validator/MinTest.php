<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Validator_MinTest extends PHPUnit_Framework_TestCase
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
        $this->vld = $plugin->getPlugin('Validator', 'Min');
    }

    public function test_min_integer()
    {
        $form_int = array(
            'type'          => VAR_TYPE_INT,
            'required'      => true,
            'min'           => '10',
            'error'         => '{form}には10以上の数字(整数)を入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_int', $form_int);

        $pear_error = $this->vld->validate('namae_int', 12, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', 10, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', '', $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_int', 10.5, $form_int);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // minより小さい値
        $pear_error = $this->vld->validate('namae_int', -2, $form_int);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_INT,$pear_error->getCode());
        $this->assertEquals($form_int['error'], $pear_error->getMessage());
    }

    public function test_min_float()
    {
        $form_float = array(
            'type'          => VAR_TYPE_FLOAT,
            'required'      => true,
            'min'           => '10.000000',
            'error'         => '{form}には10.000000以上の数字(小数)を入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_float', $form_float);

        $pear_error = $this->vld->validate('namae_float', 10.0, $form_float);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_float', '', $form_float);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // minより小さい値
        $pear_error = $this->vld->validate('namae_float', 9.11, $form_float);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_FLOAT, $pear_error->getCode());
        $this->assertEquals($form_float['error'], $pear_error->getMessage());

        // minより小さい値
        $pear_error = $this->vld->validate('namae_float', 9, $form_float);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_FLOAT, $pear_error->getCode());
        $this->assertEquals($form_float['error'], $pear_error->getMessage());
    }

    public function test_min_string()
    {
        $form_string = array(
            'type'          => VAR_TYPE_STRING,
            'required'      => true,
            'min'           => '2',
            'error'         => '{form}は全角2文字以上(半角1文字以上)で入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_string', $form_string);

        $pear_error = $this->vld->validate('namae_string', 'ddd', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', '', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', 'd', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_STRING, $pear_error->getCode());
        $this->assertEquals($form_string['error'], $pear_error->getMessage());

        // minを短い文字列長
        $pear_error = $this->vld->validate('namae_string', 8, $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_STRING, $pear_error->getCode());
        $this->assertEquals($form_string['error'], $pear_error->getMessage());

        // multibyte string.
        $pear_error = $this->vld->validate('namae_string', 'ああ', $form_string);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_string', 'あ', $form_string);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
    }

    public function test_min_datetime()
    {
        $form_datetime = array(
            'type'          => VAR_TYPE_DATETIME,
            'required'      => true,
            'min'           => '-1 day',
            'error'         => '{form}には-1 day以降の日付を入力して下さい'
        );
        $af = $this->controller->getActionForm();
        $af->setDef('namae_datetime', $form_datetime);

        $pear_error = $this->vld->validate('namae_datetime', '+2 day', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_datetime', '-1 day', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        $pear_error = $this->vld->validate('namae_datetime', '', $form_datetime);
        $this->assertFalse(is_a($pear_error, 'Ethna_Error'));

        // minより古い日付
        $pear_error = $this->vld->validate('namae_datetime', '-3 day', $form_datetime);
        $this->assertTrue(is_a($pear_error, 'Ethna_Error'));
        $this->assertEquals(E_FORM_MIN_DATETIME, $pear_error->getCode());
        $this->assertEquals($form_datetime['error'], $pear_error->getMessage());
    }
}

