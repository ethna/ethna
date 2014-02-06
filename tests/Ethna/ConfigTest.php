<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Config_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();

        // etcディレクトリを上書き
        $this->controller->setDirectory('etc', dirname(__FILE__));
        $this->config = $this->controller->getConfig();
        $this->filename = dirname(__FILE__) . '/ethna-ini.php';
    }

    public function tearDown()
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public function test_getConfigFile()
    {
        $result = $this->config->_getConfigFile();
        $this->assertEquals($result, $this->filename);
    }

    public function test_update()
    {
        // この時点ではまだ ethna-ini.php は存在しない
        $result = $this->config->get('foo');
        $this->assertEquals($result, null);

        // Ethna_Configオブジェクト内の値
        $this->config->set('foo', 'bar');
        $result = $this->config->get('foo');
        $this->assertEquals($result, 'bar');

        // ethna-ini.php が自動生成される
        $this->config->update();

        // ethna-ini.php を読み込み直す
        $this->config->_getConfig();
        $result = $this->config->get('foo');
        $this->assertEquals($result, 'bar');

        // 値を上書き
        $this->config->set('foo', 'baz');
        $this->config->update();

        // もう一度読み込み直す
        $this->config->_getConfig();
        $result = $this->config->get('foo');
        $this->assertEquals($result, 'baz');
    }
}
