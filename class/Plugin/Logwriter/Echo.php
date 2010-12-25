<?php
// vim: foldmethod=marker
/**
 *  Echo.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Logwriter_Echo
/**
 *  ログ出力基底クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Logwriter_Echo extends Ethna_Plugin_Logwriter
{
    /**#@+
     *  @access private
     */

    /**#@-*/

    /**
     *  ログを出力する
     *
     *  @access public
     *  @param  int     $level      ログレベル(LOG_DEBUG, LOG_NOTICE...)
     *  @param  string  $message    ログメッセージ(+引数)
     */
    function log($level, $message)
    {
        $c = Ethna_Controller::getInstance();

        $prefix = $this->ident;
        if (array_key_exists("pid", $this->option)) {
            $prefix .= sprintf('[%d]', getmypid());
        }
        $prefix .= sprintf($c->getGateway() != GATEWAY_WWW ? '(%s): ' : '(<b>%s</b>): ',
            $this->_getLogLevelName($level)
        );
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

        $br = $c->getGateway() != GATEWAY_WWW ? "" : "<br />";
        echo($prefix . $message . $br . "\n");

        return $prefix . $message;
    }
}
// }}}
