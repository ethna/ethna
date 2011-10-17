<?php
/**
 *  Plugin_Logwriter_File_Test.php
 */

/**
 *  Ethna_Plugin_Logwriter_Fileクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Logwriter_File_Test extends Ethna_UnitTestBase
{
    function testLogwriterFile()
    {
        $ctl = Ethna_Controller::getInstance();
        $plugin = $ctl->getPlugin();
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
        $this->assertTrue(preg_match('/^'.preg_quote(strftime('%Y/%m/%d %H:%M:'), '/')
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
        $this->assertTrue(preg_match('/^'.preg_quote(strftime('%Y/%m/%d %H:%M:'), '/')
                            .'[0-5][0-9] '
                            .preg_quote($option['ident'].'['.getmypid().']('
                                        .$lw->_getLogLevelName($level).'): '
                                        .$message)
                            .'/', trim($file[$line_count - 1])));

        unlink($option['file']);

    }
}
