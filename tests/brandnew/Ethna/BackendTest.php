<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

use Prophecy\PhpUnit\ProphecyTestCase;

/**
 * このテストケースはEthna_Backendを最低限つくるにはどうすればできるか、
 * ということをチェックします。
 *
 * @package Ethna
 */
class Ethna_BackendTest extends ProphecyTestCase
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    /** @var  Ethna_Config $config */
    protected $config;

    /** @var  Ethna_I18N $i18n */
    protected $i18n;

    /** @var  Ethna_Session $session */
    protected $session;

    /** @var  Ethna_Plugin $plugin */
    protected $plugin;

    /** @var  Ethna_Logger $logger */
    protected $logger;

    /** @var  Ethna_ActionForm $action_form */
    protected $action_form;

    /** @var  Ethna_ActionError $action_error */
    protected $action_error;

    /** @var  Ethna_ActionClass $action_class */
    protected $action_class;

    /** @var  Ethna_ClassFactory $class_factory */
    protected $class_factory;

    public function setup()
    {
        parent::setup();

        $this->controller = $this->prophesize("Ethna_Controller");
        $this->config = $this->prophesize("Ethna_Config");
        $this->i18n = $this->prophesize("Ethna_I18N");
        $this->action_error = $this->prophesize("Ethna_ActionError");
        $this->action_form = $this->prophesize("Ethna_ActionForm");
        $this->action_class = $this->prophesize("Ethna_ActionClass");
        $this->session = $this->prophesize("Ethna_Session");
        $this->plugin = $this->prophesize("Ethna_Plugin");
        $this->logger = $this->prophesize("Ethna_Logger");
        $this->class_factory = $this->prophesize("Ethna_ClassFactory");

        $this->controller->getClassFactory()->willReturn($this->class_factory);
        $this->controller->getConfig()->willReturn($this->config);
        $this->controller->getI18N()->willReturn($this->i18n);
        $this->controller->getActionError()->willReturn($this->action_error);
        $this->controller->getActionForm()->willReturn($this->action_form);
        $this->controller->getSession()->willReturn($this->session);
        $this->controller->getPlugin()->willReturn($this->plugin);
        $this->controller->getLogger()->willReturn($this->logger);
    }

    public function testGetAppId()
    {
        $this->controller->getAppId()->willReturn("CHOBIE");

        $backend = new Ethna_Mock_Backend($this->controller->reveal());
        $this->assertEquals("CHOBIE", $backend->getAppId());
    }

    public function testGetBaseDir()
    {
        $this->controller->getBasedir()->willReturn("/home/ethna");

        $backend = new Ethna_Mock_Backend($this->controller->reveal());
        $this->assertEquals("/home/ethna", $backend->getBasedir());
    }

    public function testPerform()
    {
        $this->controller
            ->getActionClassName("test")
            ->willReturn("Ethna_Mock_ActionClass");

        $backend = new Ethna_Mock_Backend($this->controller->reveal());
        $this->assertEquals("index",
            $backend->perform("test"), "backend returns correct forward name");
    }
}

