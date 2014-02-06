<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_FormTemplate_ActionForm extends Ethna_ActionForm
{
    public $form_template = array(
        'normal' => array(
            'name'      => '通常のフォームテンプレート用定義',
            'required'  => false,
            'form_type' => FORM_TYPE_SELECT,
            'type'      => VAR_TYPE_INT,
        ),
        'syntax_sugar' => array(
            'name'      => 'シンタックスシュガー用定義',
            'required'  => true,
            'form_type' => FORM_TYPE_TEXT,
            'type'      => VAR_TYPE_STRING,
        ),
    );
}

class Ethna_FormTemplateTest_ActionForm extends Ethna_FormTemplate_ActionForm
{
    public $form = array(
        'normal' => array(),
        'syntax_sugar',  //  シンタックスシュガー
    );
}

class Ethna_ActionForm_FormTemplateTest extends PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $local_af;

    public function setUp()
    {
        //   REQUEST_METHOD を設定しないと
        //   フォームテンプレートが初期化されない
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->controller = new Ethna_Controller_Dummy();
        $this->local_af = new Ethna_FormTemplateTest_ActionForm($this->controller);
    }

    public function tearDown()
    {
        $_SERVER['REQUEST_METHOD'] = NULL;
        $this->local_af = NULL;
    }

    public function test_formtemplate_normal()
    {
        $normal_def = $this->local_af->getDef('normal');
        $this->assertEquals($normal_def['name'], '通常のフォームテンプレート用定義');
        $this->assertEquals($normal_def['required'], false);
        $this->assertEquals($normal_def['form_type'], FORM_TYPE_SELECT);
        $this->assertEquals($normal_def['type'], VAR_TYPE_INT);
    }

    public function test_formtemplate_syntaxsugar()
    {
        $syntax_sugar_def = $this->local_af->getDef('syntax_sugar');
        $this->assertEquals($syntax_sugar_def['name'], 'シンタックスシュガー用定義');
        $this->assertEquals($syntax_sugar_def['required'], true);
        $this->assertEquals($syntax_sugar_def['form_type'], FORM_TYPE_TEXT);
        $this->assertEquals($syntax_sugar_def['type'], VAR_TYPE_STRING);
    }
}
