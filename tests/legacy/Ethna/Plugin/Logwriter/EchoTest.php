<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Logwriter_EchoTest extends PHPUnit_Framework_TestCase
{
    protected $plugin;
    protected $log_writer;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();
        $this->url_handler = new Ethna_UrlHandler_Simple_TestClass($this);

        $this->plugin = $this->controller->getPlugin();
        $this->log_writer = $this->plugin->getPlugin('Logwriter', 'Echo');
        $option = array(
            'ident' => 'testident',
            'facility' => 'mail',
        );
        $this->log_writer->setOption($option);

    }


    public function testLogwriterEcho()
    {
        $message = 'comment';

        $level_array = array(LOG_EMERG,
            LOG_ALERT,
            LOG_CRIT,
            LOG_ERR,
            LOG_WARNING,
            LOG_NOTICE,
            LOG_INFO,
            LOG_DEBUG,);

        foreach ($level_array as $level) {
            ob_start();         // コンソールへの出力をキャプチャ開始

            // 関数が返す文字列に改行タグ付与の是非
            $funcout = $this->log_writer->log($level, $message)
                . sprintf("%s", $this->controller->getGateway() != GATEWAY_WWW ? "" : "<br />");

            $stdout = trim(ob_get_contents());
            $this->assertEquals($funcout, $stdout);

            ob_end_clean();     // コンソールへの出力をキャプチャ終了
        }
    }

    /**
     * testBug9009
     *
     * @see http://sourceforge.jp/tracker/index.php?func=detail&aid=9009&group_id=1343&atid=5092
     */
    public function testBug9009()
    {
        $level = LOG_WARNING;
        $message = "SELECT * FROM item WHERE name LIKE '%salt%';";

        ob_start();         // コンソールへの出力をキャプチャ開始

        // 関数が返す文字列に改行タグ付与の是非
        $funcout = $this->log_writer->log($level, $message)
            . sprintf("%s", $this->controller->getGateway() != GATEWAY_WWW ? "" : "<br />");

        $stdout = trim(ob_get_contents());
        $this->assertEquals($funcout, $stdout);

        ob_end_clean();     // コンソールへの出力をキャプチャ終了
    }
}

