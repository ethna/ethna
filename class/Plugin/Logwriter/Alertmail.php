<?php
// vim: foldmethod=marker
/**
 *  Alertmail.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Logwriter_Alertmail
/**
 *  ログ出力クラス(アラートメール)
 *  Ethna_Logger にある _alert() をプラグインにしただけです。
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Logwriter_Alertmail extends Ethna_Plugin_Logwriter
{
    /**#@+
     *  @access private
     */

    /** @var    array   アラート送信先メールアドレス */
    public $mailaddress = array();

    /**#@-*/

    /**
     *  ログオプションを設定する
     *
     *  @access public
     *  @param  int     $option     ログオプション(LOG_FILE,LOG_FUNCTION...)
     */
    function setOption($option)
    {
        parent::setOption($option);
        
        if (isset($option['mailaddress'])) {
            $this->mailaddress = preg_split('/\s*,\s*/',
                                            $option['mailaddress'],
                                            -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    /**
     *  ログを出力する
     *
     *  @access public
     *  @param  int     $level      ログレベル(LOG_DEBUG, LOG_NOTICE...)
     *  @param  string  $message    ログメッセージ(+引数)
     */
    function log($level, $message)
    {
        if (count($this->mailaddress) == 0) {
            return;
        }

        $prefix = $this->ident;
        if (array_key_exists("pid", $this->option)) {
            $prefix .= sprintf('[%d]', getmypid());
        }
        $prefix .= sprintf('(%s): ', $this->_getLogLevelName($level));
        if (array_key_exists("function", $this->option) ||
            array_key_exists("pos", $this->option)) {
            $tmp = "";
            $bt = $this->_getBacktrace();
            if ($bt && $bt['function']
                && array_key_exists("function", $this->option) ) {
                $tmp .= $bt['function'];
            }
            if ($bt && array_key_exists("pos", $this->option) && $bt['pos']) {
                $tmp .= $tmp ? sprintf('(%s)', $bt['pos']) : $bt['pos'];
            }
            if ($tmp) {
                $prefix .= $tmp . ": ";
            }
        }

        $this->_alert($prefix . $message . "\n");

        return $prefix . $message;
    }

    /**
     *  メールを送信する
     *
     *  @access protected
     *  @param  string  $message    ログメッセージ
     */
    function _alert($message)
    {
        restore_error_handler();

        $c = Ethna_Controller::getInstance();
        $appid = $c->getAppId();

        $header = "Mime-Version: 1.0\n";
        $header .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
        $header .= "X-Alert: " . $appid;
        $subject = sprintf("[%s] alert (%s%s)\n", $appid,
                           substr($message, 0, 12),
                           strlen($message) > 12 ? "..." : "");

        $message = sprintf("--- [log message] ---\n%s\n\n", $message);
        if (function_exists("debug_backtrace")) {
            $bt = debug_backtrace();
            $message .= sprintf("--- [backtrace] ---\n%s\n", Ethna_Util::FormatBacktrace($bt));
        }
        
        foreach ($this->mailaddress as $address) {
            mail($address,
                 $subject,
                 mb_convert_encoding($message, "ISO-2022-JP"),
                 $header);
        }

        set_error_handler("ethna_error_handler");
    }
}
// }}}
