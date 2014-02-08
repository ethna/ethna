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
class Ethna_ActionClassTest2 extends ProphecyTestCase
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    /** @var  Ethna_Backend $backend */
    protected $backend;

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

    public function setup()
    {
        parent::setup();

        $this->controller = $this->prophesize("Ethna_Controller");
        $this->backend = $this->prophesize("Ethna_Backend");
        $this->config = $this->prophesize("Ethna_Config");
        $this->i18n = $this->prophesize("Ethna_I18N");
        $this->action_error = $this->prophesize("Ethna_ActionError");
        $this->action_form = $this->prophesize("Ethna_ActionForm");
        $this->session = $this->prophesize("Ethna_Session");
        $this->plugin = $this->prophesize("Ethna_Plugin");
        $this->logger = $this->prophesize("Ethna_Logger");

        $this->backend->getLogger()->willReturn($this->logger);
        $this->backend->getPlugin()->willReturn($this->plugin);
        $this->backend->getSession()->willReturn($this->session);
        $this->backend->getActionForm()->willReturn($this->action_form);
        $this->backend->getActionError()->willReturn($this->action_error);
        $this->backend->getI18N()->willReturn($this->i18n);
        $this->backend->getConfig()->willReturn($this->config);
        $this->backend->getController()->willReturn($this->controller);
        $this->controller->getBackend()->willReturn($this->backend);
    }

    /**
     * check Ethna_ActionClass::perform requirements
     */
    public function testPerform()
    {
        $controller = $this->controller->reveal();

        $action = new Ethna_Mock_ActionClass($controller->getBackend());
        $this->assertEquals("index", $action->perform(), "should return index");
    }

    /**
     * check Ethna_ActionClass::authenticate requirements
     */
    public function testAuthenticate()
    {
        $controller = $this->controller->reveal();

        $action = new Ethna_Mock_ActionClass($controller->getBackend());
        $this->assertNull($action->authenticate(), "should return true");
    }

    /**
     * check Ethna_ActionClass::prepare requirements
     */
    public function testPrepare()
    {
        $controller = $this->controller->reveal();

        $action = new Ethna_Mock_ActionClass($controller->getBackend());
        $this->assertNull($action->prepare(), "should return null");
    }

}