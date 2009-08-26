<?php
// vim: foldmethod=marker
/**
 *  Syslog.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Logwriter_Syslog
/**
 *  ログ出力クラス(Syslog)
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Logwriter_Syslog extends Ethna_Plugin_Logwriter
{
    /**
     *  ログ出力を開始する
     *
     *  @access public
     */
    function begin()
    {
        // syslog用オプションのみを指定
        if (array_key_exists("pid", $this->option)) {
            $option = $this->option & (LOG_PID);
        }
        openlog($this->ident, $option, $this->facility);
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
        $prefix = sprintf('%s: ', $this->_getLogLevelName($level));
        if (array_key_exists("function", $this->option) ||
            array_key_exists("pos", $this->option)) {
            $tmp = "";
            $bt = $this->_getBacktrace();
            if ($bt && array_key_exists("function", $this->option) && $bt['function']) {
                $tmp .= $bt['function'];
            }
            if ($bt && array_key_exists("pos", $this->option) && $bt['pos']) {
                $tmp .= $tmp ? sprintf('(%s)', $bt['pos']) : $bt['pos'];
            }
            if ($tmp) {
                $prefix .= $tmp . ": ";
            }
        }
        syslog($level, $prefix . $message);

        return $prefix . $message;
    }

    /**
     *  ログ出力を終了する
     *
     *  @access public
     */
    function end()
    {
        closelog();
    }
}
// }}}
