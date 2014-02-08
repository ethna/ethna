<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

use Prophecy\PhpUnit\ProphecyTestCase;

/**
 * このテストケースはEthna_Actionを最低限つくるにはどうすればできるか、
 * ということをチェックします。
 *
 * @package Ethna
 */
class Ethna_ActionFormTest2 extends ProphecyTestCase
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    /** @var  Ethna_Backend $backend */
    protected $backend;

    /** @var  Ethna_I18N $i18n */
    protected $i18n;

    /** @var  Ethna_Plugin $plugin */
    protected $plugin;

    /** @var  Ethna_Logger $logger */
    protected $logger;

    /** @var  Ethna_ActionError $action_error */
    protected $action_error;

    /** @var  Ethna_ActionClass $action_class */
    protected $action_class;

    public function setup()
    {
        parent::setup();

        $this->controller = $this->prophesize("Ethna_Controller");
        $this->backend = $this->prophesize("Ethna_Backend");
        $this->i18n = $this->prophesize("Ethna_I18N");
        $this->action_error = $this->prophesize("Ethna_ActionError");
        $this->plugin = $this->prophesize("Ethna_Plugin");
        $this->logger = $this->prophesize("Ethna_Logger");

        $this->controller->getBackend()->willReturn($this->backend);
        $this->controller->getActionError()->willReturn($this->action_error);
        $this->controller->getI18N()->willReturn($this->i18n);
        $this->controller->getLogger()->willReturn($this->logger);
        $this->controller->getPlugin()->willReturn($this->plugin);
    }

    /**
     * @dataProvider provideGet
     */
    public function testGet($method, $requests, $value, $key, $error)
    {
        $controller = $this->controller->reveal();

        $action_form = new Ethna_Mock_ActionForm($controller);

        $_SERVER["REQUEST_METHOD"] = $method;
        if ($method == "GET") {
            $_GET = $requests;
        } else if ($method == "POST") {
            $_POST = $requests;
        }

        $action_form->setFormVars();
        $this->assertEquals($value, $action_form->get($key), $error);
    }

    public function testGetDef()
    {
        $controller = $this->controller->reveal();
        $action_form = new Ethna_Mock_ActionForm($controller);

        $this->assertEquals(array(
            'type' => VAR_TYPE_STRING,
            'name' => 'User Name',
        ), $action_form->getDef("name"), "action form should return correct definition");
    }

    public function testGetName()
    {
        $controller = $this->controller->reveal();
        $action_form = new Ethna_Mock_ActionForm($controller);

        $this->assertEquals("User Name",
            $action_form->getName("name"), "action form should return correct name");
    }

    public function testSetGet()
    {
        $controller = $this->controller->reveal();
        $action_form = new Ethna_Mock_ActionForm($controller);

        $this->assertNull($action_form->get("name"),
            "action form should return correct value"
        );
        $action_form->set("name", "chobie");

        $this->assertEquals("chobie", $action_form->get("name"),
            "action form should return correct value"
        );
    }

    public function provideGet()
    {
        return array(
            array(
                "GET",
                array(
                    "name" => "chobie",
                ),
                "chobie",
                "name",
                "action_form should return chobie"
            ),
            array(
                "POST",
                array(
                    "name" => "chobie",
                ),
                "chobie",
                "name",
                "action_form should return chobie"
            ),
        );
    }
}