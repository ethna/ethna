<?php
/**
 *  Plugin_Logwriter_Echo_Test.php
 */

/**
 *  Ethna_Plugin_Logwriter_Echoクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Logwriter_Echo_Test extends Ethna_UnitTestBase
{
    var $plugin;
    var $lw;

    function setUp()
    {
        $this->plugin = $this->ctl->getPlugin();
        $this->lw = $this->plugin->getPlugin('Logwriter', 'Echo');

        $option = array(
                        'ident' => 'testident',
                        'facility' => 'mail',
                        );
        $this->lw->setOption($option);
    }

    function testLogwriterEcho()
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
            $funcout = $this->lw->log($level, $message)
                . sprintf("%s", $this->ctl->getGateway() != GATEWAY_WWW ? "" : "<br />");

            $stdout = trim(ob_get_contents());
            $this->assertEqual($funcout, $stdout);

            ob_end_clean();     // コンソールへの出力をキャプチャ終了
        }
    }

    /**
     * testBug9009
     *
     * @see http://sourceforge.jp/tracker/index.php?func=detail&aid=9009&group_id=1343&atid=5092
     */
    function testBug9009()
    {
        $level = LOG_WARNING;
        $message = "SELECT * FROM item WHERE name LIKE '%salt%';";

        ob_start();         // コンソールへの出力をキャプチャ開始

        // 関数が返す文字列に改行タグ付与の是非
        $funcout = $this->lw->log($level, $message)
            . sprintf("%s", $this->ctl->getGateway() != GATEWAY_WWW ? "" : "<br />");

        $stdout = trim(ob_get_contents());
        $this->assertEqual($funcout, $stdout);

        ob_end_clean();     // コンソールへの出力をキャプチャ終了
    }
}
