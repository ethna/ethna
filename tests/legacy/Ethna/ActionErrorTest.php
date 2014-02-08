<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ActionErrorTest extends PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $form;
    protected $ae;
    protected $error_obj;
    protected $error_form_name;
    protected $error_form_name1;
    protected $message;
    protected $message1;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);

        $this->ae = new Ethna_ActionError();
        $this->error_form_name = "hoge";
        $this->message = "test error";
        $this->error_form_name1 = "tititi";
        $this->message1 = "test error1";

        $this->error_obj = new Ethna_Error(
            $this->message1,
            E_NOTICE,
            E_GENERAL
        );

        //    add dummy error object.
        $this->ae->add($this->error_form_name,
            $this->message,
            E_GENERAL
        );
        $this->ae->addObject($this->error_form_name1,
            $this->error_obj
        );
    }

    public function test_count()
    {
        $this->assertEquals($this->ae->count(), 2);
    }

    public function test_length()
    {
        $this->assertEquals($this->ae->length(), 2);
    }

    public function test_iserror()
    {
        $this->assertTrue(
            $this->ae->isError($this->error_form_name)
        );
        $this->assertTrue(
            $this->ae->isError($this->error_form_name1)
        );
    }

    public function test_geterrorlist()
    {
        $this->assertTrue(
            is_array($this->ae->getErrorList())
        );
    }

    public function test_getmessage()
    {
        $error_msg = $this->ae->getMessage(
            $this->error_form_name
        );
        $error_msg1 = $this->ae->getMessage(
            $this->error_form_name1
        );

        $this->assertEquals($this->message, $error_msg);
        $this->assertEquals($this->message1, $error_msg1);
    }

    public function test_clear()
    {
        $this->ae->clear();
        $this->assertTrue(
            $this->ae->count() == 0
        );
    }

}

