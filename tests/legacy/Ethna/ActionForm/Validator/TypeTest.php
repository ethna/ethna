<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ActionForm_Validator_TypeTest extends PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $af;
    protected $ae;

    public function setUp()
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

    public function test_Validate_Type_Integer()
    {
        $form_def = array(
            'type' => VAR_TYPE_INT,
        );
        $this->af->setDef('input', $form_def);

        $this->af->set('input', 5);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 6.5);
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }

    public function test_Validate_Type_Float()
    {
        $form_def = array(
            'type' => VAR_TYPE_FLOAT,
        );
        $this->af->setDef('input', $form_def);

        $this->af->set('input', 4.999999);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 4);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
    }

    public function test_Validate_Type_DateTime()
    {
        $form_def = array(
            'type' => VAR_TYPE_DATETIME,
        );
        $this->af->setDef('input', $form_def);

        $this->af->set('input', '1999-12-31');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', ';-!#');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();
    }

    public function test_Validate_Min_String()
    {
        $form_def = array(
            'type' => VAR_TYPE_STRING,
        );
        $this->af->setDef('input', $form_def);

        //   in ascii.
        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   multibyte.
        $this->af->set('input', 'あいうえお');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    空文字の値はTypeではチェックしない
        $this->af->set('input', '');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();
    }
}


