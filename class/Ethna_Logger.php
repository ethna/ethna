<?php
// vim: foldmethod=marker
/**
 *  Ethna_Logger.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  拡張ログプロパティ: ファイル出力
 */
define('LOG_FILE', 1 << 16);

/**
 *  拡張ログプロパティ: 標準出力
 */
define('LOG_ECHO', 1 << 17);


// {{{ ethna_error_handler
/**
 *  エラーコールバック関数
 *
 *  @param  int     $errno      エラーレベル
 *  @param  string  $errstr     エラーメッセージ
 *  @param  string  $errfile    エラー発生箇所のファイル名
 *  @param  string  $errline    エラー発生箇所の行番号
 */
function ethna_error_handler($errno, $errstr, $errfile, $errline)
{
    if ($errno == E_STRICT) {
        // E_STRICTは表示しない
        return E_STRICT;
    }

    list($level, $name) = Ethna_Logger::errorLevelToLogLevel($errno);
    switch ($errno) {
    case E_ERROR:
    case E_CORE_ERROR:
    case E_COMPILE_ERROR:
    case E_USER_ERROR:
        $php_errno = 'Fatal error'; break;
    case E_WARNING:
    case E_CORE_WARNING:
    case E_COMPILE_WARNING:
    case E_USER_WARNING:
        $php_errno = 'Warning'; break;
    case E_PARSE:
        $php_errno = 'Parse error'; break;
    case E_NOTICE:
    case E_USER_NOTICE:
        $php_errno = 'Notice'; break;
    default:
        $php_errno = 'Unknown error'; break;
    }

    $php_errstr = sprintf('PHP %s: %s in %s on line %d', $php_errno, $errstr, $errfile, $errline);
    if (ini_get('log_errors') && (error_reporting() & $errno)) {
        $locale = setlocale(LC_TIME, 0);
        setlocale(LC_TIME, 'C');
        error_log($php_errstr, 0);
        setlocale(LC_TIME, $locale);
    }

    $c =& Ethna_Controller::getInstance();
    $logger =& $c->getLogger();
    $logger->log($level, sprintf("[PHP] %s: %s in %s on line %d", $name, $errstr, $errfile, $errline));

    $facility = $logger->getLogFacility();
    $has_echo = false;
    if ((is_array($facility) && in_array(LOG_ECHO, $facility)) || (is_array($facility) == false && $facility == LOG_ECHO)) {
        $has_echo = true;
    }

    if ($has_echo == false && ini_get('display_errors') && (error_reporting() & $errno)) {
        if ($c->getGateway() != GATEWAY_WWW) {
            $format = "%s: %s in %s on line %d\n";
        } else {
            $format = "<b>%s</b>: %s in <b>%s</b> on line <b>%d</b><br />\n";
        }
        printf($format, $php_errno, $errstr, $errfile, $errline);
    }
}
// }}}

// {{{ Ethna_Logger
/**
 *  ログ管理クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Logger extends Ethna_AppManager
{
    /**#@+
     *  @access private
     */

    /** @var    array   ログファシリティ一覧 */
    var $log_facility_list = array(
        'auth'      => array('name' => 'LOG_AUTH'),
        'cron'      => array('name' => 'LOG_CRON'),
        'daemon'    => array('name' => 'LOG_DAEMON'),
        'kern'      => array('name' => 'LOG_KERN'),
        'lpr'       => array('name' => 'LOG_LPR'),
        'mail'      => array('name' => 'LOG_MAIL'),
        'news'      => array('name' => 'LOG_NEWS'),
        'syslog'    => array('name' => 'LOG_SYSLOG'),
        'user'      => array('name' => 'LOG_USER'),
        'uucp'      => array('name' => 'LOG_UUCP'),
        'file'      => array('name' => 'LOG_FILE'),
        'echo'      => array('name' => 'LOG_ECHO'),
    );

    /** @var    array   ログレベル一覧 */
    var $log_level_list = array(
        'emerg'     => array('name' => 'LOG_EMERG',     'value' => 7),
        'alert'     => array('name' => 'LOG_ALERT',     'value' => 6),
        'crit'      => array('name' => 'LOG_CRIT',      'value' => 5),
        'err'       => array('name' => 'LOG_ERR',       'value' => 4),
        'warning'   => array('name' => 'LOG_WARNING',   'value' => 3),
        'notice'    => array('name' => 'LOG_NOTICE',    'value' => 2),
        'info'      => array('name' => 'LOG_INFO',      'value' => 1),
        'debug'     => array('name' => 'LOG_DEBUG',     'value' => 0),
    );

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    var $controller;

    /** @var    object  Ethna_Controller    controllerオブジェクト($controllerの省略形) */
    var $ctl;

    /** @var    array   ログファシリティ */
    var $facility = array();

    /** @var    array   ログレベル */
    var $level = array();

    /** @var    array   ログオプション */
    var $option = array();

    /** @var    array   メッセージフィルタ(出力) */
    var $message_filter_do = array();

    /** @var    array   メッセージフィルタ(無視) */
    var $message_filter_ignore = array();

    /** @var    int     アラートレベル */
    var $alert_level;

    /** @var    string  アラートメールアドレス */
    var $alert_mailaddress;

    /** @var    array   Ethna_LogWriter ログ出力オブジェクト */
    var $writer;

    /** @var    bool    ログ出力開始フラグ */
    var $is_begin = false;

    /** @var    array   ログスタック(begin()前にlog()が呼び出された場合のスタック) */
    var $log_stack = array();

    /**#@-*/
    
    /**
     *  Ethna_Loggerクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller controllerオブジェクト
     */
    function Ethna_Logger(&$controller)
    {
        $this->controller =& $controller;
        $this->ctl =& $this->controller;
        $config =& $controller->getConfig();

        // ログファシリティテーブル補完(LOCAL0〜LOCAL8)
        for ($i = 0; $i < 8; $i++) {
            if (defined("LOG_LOCAL$i")) {
                $this->log_facility_list["local$i"] = array('name' => "LOG_LOCAL$i");
            }
        }

        // ログファシリティ
        $this->facility = $this->_parseLogFacility($config->get('log_facility'));

        foreach ($this->facility as $f) {
            // ログレベル
            $level = $this->_parseLogLevel($config->get("log_level_$f"));
            if (is_null($level)) {
                $level = $this->_parseLogLevel($config->get("log_level"));
            }
            if (is_null($level)) {
                $level = LOG_WARNING;
            }
            $this->level[$f] = $level;

            // ログオプション
            $option = $this->_parseLogOption($config->get("log_option_$f"));
            if (is_null($option)) {
                $option = $this->_parseLogOption($config->get("log_option"));
            }
            $this->option[$f] = $option;

            // メッセージフィルタ
            $message_filter_do = $config->get("log_filter_do_$f");
            if (is_null($message_filter_do)) {
                $message_filter_do = $config->get("log_filter_do");
            }
            $this->message_filter_do[$f] = $message_filter_do;

            $message_filter_ignore = $config->get("log_filter_ignore_$f");
            if (is_null($message_filter_ignore)) {
                $message_filter_ignore = $config->get("log_filter_ignore");
            }
            $this->message_filter_ignore[$f] = $message_filter_ignore;
        }

        // アラートオプション
        $this->alert_level = $this->_parseLogLevel($config->get('log_alert_level'));
        $this->alert_mailaddress = preg_split('/\s*,\s*/', $config->get('log_alert_mailaddress'));

        set_error_handler("ethna_error_handler");
    }

    /**
     *  ログファシリティを取得する
     *
     *  @access public
     *  @return mixed   ログファシリティ(ファシリティが1つ以下ならscalar、2つ以上なら配列を返す for B.C.)
     */
    function getLogFacility()
    {
        if (is_array($this->facility)) {
            if (count($this->facility) == 0) {
                return null;
            } else if (count($this->facility) == 1) {
                return $this->facility[0];
            }
        }
        return $this->facility;
    }

    /**
     *  PHPエラーレベルをログレベルに変換する
     *
     *  @access public
     *  @param  int     $errno  PHPエラーレベル
     *  @return array   ログレベル(LOG_NOTICE,...), エラーレベル表示名("E_NOTICE"...)
     *  @static
     */
    function errorLevelToLogLevel($errno)
    {
        switch ($errno) {
        case E_ERROR:           $code = "E_ERROR"; $level = LOG_ERR; break;
        case E_WARNING:         $code = "E_WARNING"; $level = LOG_WARNING; break;
        case E_PARSE:           $code = "E_PARSE"; $level = LOG_CRIT; break;
        case E_NOTICE:          $code = "E_NOTICE"; $level = LOG_NOTICE; break;
        case E_USER_ERROR:      $code = "E_USER_ERROR"; $level = LOG_ERR; break;
        case E_USER_WARNING:    $code = "E_USER_WARNING"; $level = LOG_WARNING; break;
        case E_USER_NOTICE:     $code = "E_USER_NOTICE"; $level = LOG_NOTICE; break;
        case E_STRICT:          $code = "E_STRING"; $level = LOG_NOTICE; return;
        default:                $code = "E_UNKNOWN"; $level = LOG_DEBUG; break;
        }
        return array($level, $code);
    }

    /**
     *  ログ出力を開始する
     *
     *  @access public
     */
    function begin()
    {
        // LogWriterクラスの生成
        foreach ($this->facility as $f) {
            $this->writer[$f] =& $this->_getLogWriter($this->option[$f], $f);
            if (Ethna::isError($this->writer[$f])) {
                // use default
                $this->writer[$f] =& $this->_getLogWriter($this->option[$f], "default");
            }
        }

        foreach (array_keys($this->writer) as $key) {
            $this->writer[$key]->begin();
        }
        
        $this->is_begin = true;

        // begin()以前のlog()を処理
        if (count($this->log_stack) > 0) {
            // copy and clear for recursive calls
            $tmp_stack = $this->log_stack;
            $this->log_stack = array();

            while (count($tmp_stack) > 0) {
                $log = array_shift($tmp_stack);
                $this->log($log[0], $log[1]);
            }
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
        if ($this->is_begin == false) {
            $args = func_get_args();
            if (count($args) > 2) {
                array_splice($args, 0, 2);
                $message = vsprintf($message, $args);
            }
            $this->log_stack[] = array($level, $message);
            return;
        }

        foreach (array_keys($this->writer) as $key) {
            // ログメッセージフィルタ(レベルフィルタに優先する)
            $r = $this->_evalMessageMask($this->message_filter_do[$key], $message);
            if (is_null($r)) {
                $r = $this->_evalMessageMask($this->message_filter_ignore[$key], $message);
                if ($r) {
                    continue;
                }
            }

            // ログレベルフィルタ
            if ($this->_evalLevelMask($this->level[$key], $level)) {
                continue;
            }

            // ログ出力
            $args = func_get_args();
            if (count($args) > 2) {
                array_splice($args, 0, 2);
                $message = vsprintf($message, $args);
            }
            $output = $this->writer[$key]->log($level, $message);
        }

        // アラート処理
        if ($this->_evalLevelMask($this->alert_level, $level) == false) {
            if (count($this->alert_mailaddress) > 0) {
                $this->_alert($output);
            }
        }
    }

    /**
     *  ログ出力を終了する
     *
     *  @access public
     */
    function end()
    {
        foreach (array_keys($this->writer) as $key) {
            $this->writer[$key]->end();
        }

        $this->is_begin = false;
    }

    /**
     *  LogWriterオブジェクトを取得する
     *
     *  @access protected
     *  @param  array   $option     ログオプション
     *  @param  string  $facility   ログファシリティ
     *  @return object  LogWriter   LogWriterオブジェクト
     */
    function &_getLogWriter($option, $facility = null)
    {
        if ($facility == null) {
            $facility = $this->getLogFacility();
            if (is_array($facility)) {
                $facility = $facility[0];
            }
        }

        if (is_null($facility)) {
            $plugin = "default";
        } else if (isset($this->log_facility_list[$facility])) {
            if ($facility == "file" || $facility == "echo") {
                $plugin = $facility;

            } else {
                $plugin = "syslog";
            }
        } else {
            $plugin = $facility;
        }

        $plugin_manager =& $this->controller->getPlugin();
        $plugin_object = $plugin_manager->getPlugin('Logwriter', ucfirst(strtolower($plugin)));
        if (Ethna::isError($plugin_object)) {
            return $plugin_object;
        }

        if (isset($option['ident']) == false) {
            $option['ident'] = $this->controller->getAppId();
        }
        if (isset($option['facility']) == false) {
            $option['facility'] = $facility;
        }
        $plugin_object->setOption($option);

        return $plugin_object;
    }

    /**
     *  ログオプション(設定ファイル値)を解析する
     *
     *  @access private
     *  @param  string  $string ログオプション(設定ファイル値)
     *  @return array   解析された設定ファイル値(アラート通知メールアドレス, アラート対象ログレベル, ログオプション)
     */
    function _parseLogOption($string)
    {
        if (is_null($string)) {
            return null;
        }

        $option = array();
        $elts = preg_split('/\s*,\s*/', $string);
        foreach ($elts as $elt) {
            if (preg_match('/^(.*?)\s*:\s*(.*)/', $elt, $match)) {
                $option[$match[1]] = $match[2];
            } else {
                $option[$elt] = true;
            }
        }

        return $option;
    }

    /**
     *  アラートメールを送信する
     *
     *  @access protected
     *  @param  string  $message    ログメッセージ
     *  @return int     0:正常終了
     */
    function _alert($message)
    {
        restore_error_handler();

        // ヘッダ
        $header = "Mime-Version: 1.0\n";
        $header .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
        $header .= "X-Alert: " . $this->controller->getAppId();
        $subject = sprintf("[%s] alert (%s%s)\n", $this->controller->getAppId(), substr($message, 0, 12), strlen($message) > 12 ? "..." : "");
        
        // 本文
        $mail = sprintf("--- [log message] ---\n%s\n\n", $message);
        if (function_exists("debug_backtrace")) {
            $bt = debug_backtrace();
            $mail .= sprintf("--- [backtrace] ---\n%s\n", Ethna_Util::FormatBacktrace($bt));
        }

        foreach ($this->alert_mailaddress as $mailaddress) {
            mail($mailaddress, $subject, mb_convert_encoding($mail, "ISO-2022-JP"), $header);
        }

        set_error_handler("ethna_error_handler");

        return 0;
    }

    /**
     *  ログメッセージのマスクチェックを行う
     *
     *  @access private
     *  @param  string  $filter     フィルタ
     *  @param  string  $message    ログメッセージ
     *  @return mixed   true:match, null:skip
     */
    function _evalMessageMask($filter, $message)
    {
        $regexp = sprintf("/%s/", $filter);

        if ($filter && preg_match($regexp, $message)) {
            return true;
        }

        return null;
    }

    /**
     *  ログレベルのマスクチェックを行う
     *
     *  @access private
     *  @param  int     $src    ログレベルマスク
     *  @param  int     $dst    ログレベル
     *  @return bool    true:閾値以下 false:閾値以上
     */
    function _evalLevelMask($src, $dst)
    {
        static $log_level_table = null;

        if (is_null($log_level_table)) {
            $log_level_table = array();

            // ログレベルテーブル(逆引き)作成
            foreach ($this->log_level_list as $key => $def) {
                if (defined($def['name']) == false) {
                    continue;
                }
                $log_level_table[constant($def['name'])] = $def['value'];
            }
        }

        // 知らないレベルなら出力しない
        if (isset($log_level_table[$src]) == false || isset($log_level_table[$dst]) == false) {
            return true;
        }

        if ($log_level_table[$dst] >= $log_level_table[$src]) {
            return false;
        }

        return true;
    }

    /**
     *  ログファシリティ(設定ファイル値)を解析する
     *
     *  @access private
     *  @param  string  $facility   ログファシリティ(設定ファイル値)
     *  @return array   ログファシリティ(LOG_LOCAL0, LOG_FILE...)を格納した配列
     */
    function _parseLogFacility($facility)
    {
        $facility_list = preg_split('/\s*,\s*/', $facility);
        if (is_array($facility_list) == false) {
            // no way
            return array();
        }

        return $facility_list;
    }

    /**
     *  ログレベル(設定ファイル値)を解析する
     *
     *  @access private
     *  @param  string  $level  ログレベル(設定ファイル値)
     *  @return int     ログレベル(LOG_DEBUG, LOG_NOTICE...)
     */
    function _parseLogLevel($level)
    {
        if (isset($this->log_level_list[strtolower($level)]) == false) {
            return null;
        }
        $constant_name = $this->log_level_list[strtolower($level)]['name'];

        return constant($constant_name);
    }
}
// }}}
?>
