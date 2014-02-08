<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ActionForm_Validator_MbregexpTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->ae = $this->controller->getActionError();
        $this->af = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->af);
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->setDef(null, array());
        $this->ae->clear();
    }

    function test_Validate_Regexp()
    {
        $form_def = array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'required' => true,
            'mbregexp' => '^[あ-ん]+$',
        );
        $this->af->setDef('input', $form_def);

        $this->af->set('input', 'a5A4Pgw9');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'あいうえおかきくけこ');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 1459);
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        //    encoding に指定された文字コード以外の文字列
        $euc_input = mb_convert_encoding('あいうえお', 'EUC-JP', 'UTF-8');
        $this->af->set('input', $euc_input);
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
}

