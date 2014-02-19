<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Logwriter_FileTest extends PHPUnit_Framework_TestCase
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
        $option = array(
            'ident' => 'testident',
            'facility' => 'mail',
        );
        $this->log_writer->setOption($option);

    }

    public function testLogwriterFile()
    {
        $plugin = $this->controller->getPlugin();
        $lw = $plugin->getPlugin('Logwriter', 'File');

        $option = array(
            'ident'    => 'hoge',
            'facility' => 'mail',
            'file'     => 'logfile',
            'mode'     => '0666',
        );
        $lw->setOption($option);

        $level = LOG_WARNING;
        $message = 'comment';
        $lw->begin();
        $_before_size = filesize($option['file']);
        $this->assertTrue(file_exists($option['file']));
        $lw->log($level, $message);
        $lw->end();
        clearstatcache();
        $_after_size = filesize($option['file']);
        // ログを出力したファイルのサイズが大きくなったことを確認
        $this->assertTrue($_before_size < $_after_size);

        $file = file($option['file']);
        $line_count = count($file); // 最後に追記した行番号
        // 年月日時分の一致、秒のフォーマット、ID・ログレベル・メッセージの一致を確認
        $this->assertTrue((bool)preg_match('/^'.preg_quote(strftime('%Y/%m/%d %H:%M:'), '/')
            .'[0-5][0-9] '
            .preg_quote($option['ident'].'('
                .$lw->_getLogLevelName($level).'): '
                .$message)
            .'/', trim($file[$line_count - 1])));


        $option = array(
            'pid'      => true,
            'ident'    => 'hoge',
            'facility' => 'mail',
            'file'     => 'logfile',
            'mode'     => '0666',
        );
        $lw->setOption($option);

        $level = LOG_WARNING;
        $message = 'comment';
        $lw->begin();
        $_before_size = filesize($option['file']);
        $this->assertTrue(file_exists($option['file']));
        $lw->log($level, $message);
        $lw->end();
        clearstatcache();
        $_after_size = filesize($option['file']);
        // ログを出力したファイルのサイズが大きくなったことを確認
        $this->assertTrue($_before_size < $_after_size);

        $file = file($option['file']);
        $line_count = count($file); // 最後に追記した行番号
        // 年月日時分の一致、秒のフォーマット、ID・PID・ログレベル・メッセージの一致を確認
        $this->assertTrue((bool)preg_match('/^'.preg_quote(strftime('%Y/%m/%d %H:%M:'), '/')
            .'[0-5][0-9] '
            .preg_quote($option['ident'].'['.getmypid().']('
                .$lw->_getLogLevelName($level).'): '
                .$message)
            .'/', trim($file[$line_count - 1])));

        unlink($option['file']);
    }
}

