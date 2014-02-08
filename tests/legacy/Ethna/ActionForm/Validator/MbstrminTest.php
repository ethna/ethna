<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ActionForm_Validator_MbstrminTest extends PHPUnit_Framework_TestCase
{
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

    public function test_Validate_MbMin_String()
    {
        $form_def = array(
            'type'          => VAR_TYPE_STRING,
            'form_type'     => FORM_TYPE_TEXT,
            'required'      => true,
            'mbstrmin'      => '3',
        );
        $this->af->setDef('input', $form_def);

        //   in ascii.
        $this->af->set('input', 'abc');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'ab');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   multibyte.
        $this->af->set('input', 'あいu');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'あi');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'あいuえ');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));

        //  TODO: Error Message Test.
    }
}

