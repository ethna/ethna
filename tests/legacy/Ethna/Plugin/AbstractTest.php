<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_AbstractTest extends PHPUnit_Framework_TestCase
{
    protected $plugin;
    protected $log_writer;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();

        $this->plugin = $this->controller->getPlugin();
        $this->log_writer = $this->plugin->getPlugin('Logwriter', 'Echo');

    }

    public function testDetectTypeAndName()
    {
        $this->assertEquals('logwriter', $this->log_writer->getType());
        $this->assertEquals('echo', $this->log_writer->getName());
    }
}

